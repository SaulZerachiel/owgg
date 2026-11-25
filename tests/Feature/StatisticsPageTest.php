<?php

use App\Jobs\ImportPlayerStats;
use App\Models\AllHeroesStats;
use App\Models\Hero;
use App\Models\HeroSpecificStat;
use App\Models\HeroStats;
use App\Models\Profile;
use App\Models\Rank;
use App\Models\Role;
use App\Models\RolesStats;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('a player can view every stored statistics group for their profile', function () {
    $user = User::factory()->create([
        'battletag' => 'Player-1234',
        'stats_synced_at' => now(),
    ]);
    Profile::factory()->for($user)->create([
        'player_id' => 'legacy-battletag-id',
        'name' => 'Old Player',
        'created_at' => now()->subDay(),
        'updated_at' => now()->subDay(),
    ]);
    $profile = Profile::factory()->for($user)->create([
        'player_id' => 'opaque-profile-id',
        'name' => 'Player',
    ]);
    $role = Role::factory()->create(['role_name' => 'Tank']);
    $hero = Hero::factory()->create([
        'hero_name' => 'Winston',
        'role_id' => $role->role_id,
    ]);

    Rank::factory()->create([
        'player_id' => $profile->player_id,
        'role_id' => $role->role_id,
        'season' => 'Season 1',
        'tier' => 'Diamond',
        'division' => 3,
    ]);
    RolesStats::factory()->create([
        'role_id' => $role->role_id,
        'player_id' => $profile->player_id,
        'games_played' => 42,
    ]);
    AllHeroesStats::factory()->create([
        'player_id' => $profile->player_id,
        'total_eliminations' => 1234,
    ]);
    HeroStats::factory()->create([
        'player_id' => $profile->player_id,
        'hero_id' => $hero->hero_id,
        'eliminations' => 321,
    ]);
    HeroSpecificStat::factory()->create([
        'hero_id' => $hero->hero_id,
        'player_id' => $profile->player_id,
        'stat_key' => 'charged_shots',
        'stat_value' => '7',
    ]);

    $this->actingAs($user)
        ->get(route('statistics.show', ['playerId' => $user->battletag]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Statistics/Index')
            ->where('profile.player_id', 'opaque-profile-id')
            ->where('ranks.0.role', 'Tank')
            ->where('rolesStats.0.metrics.games_played', 42)
            ->where('allHeroesStats.total_eliminations', 1234)
            ->where('heroStats.0.hero', 'Winston')
            ->where('heroStats.0.metrics.eliminations', 321)
            ->where('heroSpecificStats.0.key', 'charged_shots')
            ->where('heroSpecificStats.0.value', '7'),
        );
});

test('the statistics page shows an empty state before a profile is imported', function () {
    $user = User::factory()->create(['battletag' => 'Player-1234']);
    Queue::fake();

    $this->actingAs($user)
        ->get(route('statistics.show', ['playerId' => $user->battletag]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Statistics/Index')
            ->where('syncing', true)
            ->where('profile', null)
            ->where('ranks', [])
            ->where('heroStats', []),
        );

    Queue::assertPushed(ImportPlayerStats::class);
});

test('stale statistics are refreshed while cached values remain available', function () {
    $user = User::factory()->create([
        'battletag' => 'Player-1234',
        'stats_synced_at' => now()->subHours(2),
    ]);
    $profile = Profile::factory()->for($user)->create([
        'player_id' => 'opaque-profile-id',
    ]);
    AllHeroesStats::factory()->create([
        'player_id' => $profile->player_id,
        'total_eliminations' => 1234,
    ]);
    Queue::fake();

    $this->actingAs($user)
        ->get(route('statistics.show', ['playerId' => $user->battletag]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('syncing', true)
            ->where('profile.player_id', 'opaque-profile-id')
            ->where('allHeroesStats.total_eliminations', 1234),
        );

    Queue::assertPushed(ImportPlayerStats::class);
});

test('a player cannot view another user statistics through the route', function () {
    $user = User::factory()->create(['battletag' => 'Player-1234']);

    $this->actingAs($user)
        ->get(route('statistics.show', ['playerId' => 'Other-5678']))
        ->assertNotFound();
});

test('a player can request a manual statistics refresh with a cooldown', function () {
    $user = User::factory()->create([
        'battletag' => 'Player-1234',
        'stats_synced_at' => now(),
    ]);
    $profile = Profile::factory()->for($user)->create(['player_id' => 'opaque-id']);
    AllHeroesStats::factory()->create(['player_id' => $profile->player_id]);
    Queue::fake();

    $route = route('statistics.sync', ['playerId' => $user->battletag]);

    $this->actingAs($user)
        ->post($route)
        ->assertRedirect(route('statistics.show', ['playerId' => $user->battletag]));

    $this->post($route)
        ->assertRedirect(route('statistics.show', ['playerId' => $user->battletag]));

    $this->get(route('statistics.show', ['playerId' => $user->battletag]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('syncing', true));

    Queue::assertPushed(ImportPlayerStats::class);
    expect(Queue::pushed(ImportPlayerStats::class))->toHaveCount(1);
    expect(RateLimiter::tooManyAttempts('statistics-sync:'.$user->getKey(), 1))->toBeTrue();
});

test('a player cannot request a manual refresh for another player', function () {
    $user = User::factory()->create(['battletag' => 'Player-1234']);

    $this->actingAs($user)
        ->post(route('statistics.sync', ['playerId' => 'Other-5678']))
        ->assertNotFound();
});

test('a failed background import is reported without immediately enqueueing another job', function () {
    $user = User::factory()->create([
        'battletag' => 'Player-1234',
        'stats_synced_at' => now()->subHours(2),
    ]);
    Cache::put(
        ImportPlayerStats::syncStatusCacheKey($user->getKey()),
        'failed',
        now()->addMinutes(10),
    );
    Queue::fake();

    $this->actingAs($user)
        ->get(route('statistics.show', ['playerId' => $user->battletag]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('syncing', false));

    Queue::assertNothingPushed();
});
