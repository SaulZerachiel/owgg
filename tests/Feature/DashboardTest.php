<?php

use App\Models\AllHeroesStats;
use App\Models\Hero;
use App\Models\HeroStats;
use App\Models\Profile;
use App\Models\Rank;
use App\Models\Role;
use App\Models\RolesStats;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('profile', null)
            ->where('summary', null)
            ->where('ranks', [])
            ->where('topHeroes', [])
            ->where('progression', null),
        );
});

test('the dashboard summarizes the signed-in player stats and snapshot progression', function () {
    $user = User::factory()->create([
        'battletag' => 'Player-1234',
        'stats_synced_at' => now(),
    ]);
    $profile = Profile::factory()->for($user)->create([
        'player_id' => 'Player-1234',
        'name' => 'Player',
    ]);
    $role = Role::factory()->create([
        'role_id' => 'support',
        'role_name' => 'Support',
    ]);
    $hero = Hero::factory()->create([
        'role_id' => $role->role_id,
        'hero_name' => 'Moira',
    ]);

    Rank::factory()->create([
        'player_id' => $profile->player_id,
        'role_id' => $role->role_id,
        'season' => '25',
        'tier' => 'Diamond',
        'division' => 3,
    ]);
    RolesStats::factory()->create([
        'player_id' => $profile->player_id,
        'role_id' => $role->role_id,
        'games_played' => 20,
        'winrate' => 60,
    ]);
    AllHeroesStats::factory()->create([
        'player_id' => $profile->player_id,
        'games_played' => 20,
        'games_won' => 12,
        'games_lost' => 8,
        'winrate' => 60,
        'kda' => 3.2,
    ]);
    HeroStats::factory()->create([
        'player_id' => $profile->player_id,
        'hero_id' => $hero->hero_id,
        'games_played' => 15,
        'win_percentage' => 66.7,
        'time_played' => 12000,
    ]);

    $profile->statSnapshots()->create([
        'fingerprint' => str_repeat('a', 64),
        'snapshot' => [
            'all_heroes' => [
                'winrate' => 55,
                'deaths_avg_per_10_min' => 9,
            ],
        ],
        'captured_at' => now()->subDay(),
    ]);
    $profile->statSnapshots()->create([
        'fingerprint' => str_repeat('b', 64),
        'snapshot' => [
            'all_heroes' => [
                'winrate' => 60,
                'deaths_avg_per_10_min' => 8,
            ],
        ],
        'captured_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('profile.name', 'Player')
            ->where('summary.games_played', 20)
            ->where('summary.winrate', 60)
            ->where('ranks.0.role', 'Support')
            ->where('ranks.0.tier', 'Diamond')
            ->where('roles.0.games_played', 20)
            ->where('topHeroes.0.hero', 'Moira')
            ->where('progression.winrate.change', 5)
            ->where('progression.deathsPer10.change', -1)
            ->where('statisticsUrl', route('statistics.show', ['playerId' => 'Player-1234'])),
        );
});
