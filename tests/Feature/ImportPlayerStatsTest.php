<?php

use App\Jobs\ImportPlayerStats;
use App\Models\AllHeroesStats;
use App\Models\Hero;
use App\Models\HeroSpecificStat;
use App\Models\HeroStats;
use App\Models\Profile;
use App\Models\Rank;
use App\Models\RolesStats;
use App\Models\User;
use App\Services\OverfastProfileNotFoundException;
use App\Services\OverfastService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('BattleTag lookup imports and updates all statistics without duplicating them', function () {
    $user = User::factory()->create(['battletag' => 'Player-1234']);
    $profileRequests = 0;
    $overfastPlayerId = 'Player-1234';

    Http::fake(function (Request $request) use (&$profileRequests, $overfastPlayerId) {
        $path = parse_url($request->url(), PHP_URL_PATH);

        if ($path === '/players/Player-1234') {
            $profileRequests++;

            return Http::response([
                'summary' => [
                    'username' => $profileRequests === 1 ? 'Player' : 'Updated Player',
                    'avatar' => 'https://example.test/avatar.png',
                    'namecard' => 'https://example.test/namecard.png',
                    'title' => 'Data Broker',
                    'last_updated_at' => 1777998881,
                ],
                'stats' => [],
            ]);
        }

        if ($path === "/players/{$overfastPlayerId}/summary") {
            return Http::response([
                'competitive' => [
                    'pc' => [
                        'season' => 25,
                        'tank' => ['division' => 'diamond', 'tier' => 2],
                        'damage' => null,
                        'support' => null,
                        'open' => null,
                    ],
                    'console' => null,
                ],
            ]);
        }

        if ($path === "/players/{$overfastPlayerId}/stats/summary") {
            return Http::response([
                'general' => [
                    'games_played' => 42,
                    'games_won' => 20,
                    'games_lost' => 22,
                    'time_played' => 3600,
                    'winrate' => 47.62,
                    'kda' => 2.5,
                    'total' => [
                        'eliminations' => 300,
                        'assists' => 70,
                        'deaths' => 100,
                        'damage' => 100000,
                        'healing' => 50000,
                    ],
                    'average' => [
                        'eliminations' => 12.5,
                        'assists' => 2.9,
                        'deaths' => 4.2,
                        'damage' => 4166.67,
                        'healing' => 2083.33,
                    ],
                ],
                'roles' => [
                    'tank' => [
                        'games_played' => 42,
                        'games_won' => 20,
                        'games_lost' => 22,
                        'time_played' => 3600,
                        'winrate' => 47.62,
                        'kda' => 2.5,
                        'total' => [
                            'eliminations' => 300,
                            'assists' => 70,
                            'deaths' => 100,
                            'damage' => 100000,
                            'healing' => 50000,
                        ],
                        'average' => [
                            'eliminations' => 12.5,
                            'assists' => 2.9,
                            'deaths' => 4.2,
                            'damage' => 4166.67,
                            'healing' => 2083.33,
                        ],
                    ],
                ],
                'heroes' => [
                    'ana' => [
                        'games_played' => 5,
                        'games_won' => 3,
                        'games_lost' => 2,
                        'time_played' => 500,
                        'winrate' => 60,
                        'kda' => 3,
                        'total' => [
                            'eliminations' => 50,
                            'assists' => 20,
                            'deaths' => 10,
                            'damage' => 10000,
                            'healing' => 8000,
                        ],
                        'average' => [
                            'eliminations' => 10,
                            'assists' => 4,
                            'deaths' => 2,
                            'damage' => 2000,
                            'healing' => 1600,
                        ],
                    ],
                ],
            ]);
        }

        if ($path === "/players/{$overfastPlayerId}/stats/career") {
            return Http::response([
                'all-heroes' => [
                    'game' => [
                        'games_played' => 42,
                        'games_won' => 20,
                        'games_lost' => 22,
                        'time_played' => 3600,
                        'hero_wins' => 20,
                    ],
                    'combat' => ['eliminations' => 321, 'damage_done' => 100000],
                    'assists' => ['assists' => 70, 'healing_done' => 50000],
                ],
                'ana' => [
                    'game' => [
                        'games_played' => 5,
                        'games_won' => 3,
                        'games_lost' => 2,
                        'time_played' => 500,
                    ],
                    'combat' => ['eliminations' => 55, 'all_damage_done' => 10000],
                    'hero_specific' => ['biotic_grenade_kills' => 4],
                ],
            ]);
        }

        if ($path === '/roles') {
            return Http::response([
                ['key' => 'tank', 'name' => 'Tank', 'icon' => 'https://example.test/tank.svg'],
                ['key' => 'damage', 'name' => 'Damage', 'icon' => 'https://example.test/damage.svg'],
                ['key' => 'support', 'name' => 'Support', 'icon' => 'https://example.test/support.svg'],
            ]);
        }

        if ($path === '/heroes') {
            return Http::response([
                ['key' => 'ana', 'name' => 'Ana', 'role' => 'support'],
            ]);
        }

        return Http::response([], 404);
    });

    ImportPlayerStats::dispatchSync($user);
    ImportPlayerStats::dispatchSync($user);

    $profile = Profile::query()->sole();
    $rank = Rank::query()->sole();
    $roleStats = RolesStats::query()->sole();
    $allHeroesStats = AllHeroesStats::query()->sole();
    $heroStats = HeroStats::query()->sole();
    $heroSpecificStat = HeroSpecificStat::query()->sole();

    expect($profile->player_id)->toBe($overfastPlayerId)
        ->and($profile->user_id)->toBe($user->id)
        ->and($profile->name)->toBe('Updated Player')
        ->and($profile->avatar)->toBe('https://example.test/avatar.png')
        ->and($profile->career_url)->toBe("https://overfast-api.tekrop.fr/players/{$overfastPlayerId}")
        ->and($profile->blizzard_id)->toBeNull()
        ->and($profile->is_public)->toBeTrue()
        ->and($rank->tier)->toBe('diamond')
        ->and($rank->division)->toBe(2)
        ->and($roleStats->games_played)->toBe(42)
        ->and($allHeroesStats->eliminations)->toBe(321)
        ->and($heroStats->eliminations)->toBe(55)
        ->and($heroSpecificStat->stat_key)->toBe('biotic_grenade_kills')
        ->and($profile->last_updated_at->equalTo(Carbon::createFromTimestampUTC(1777998881)))->toBeTrue()
        ->and($user->fresh()->stats_synced_at)->not->toBeNull()
        ->and(Hero::query()->whereKey('ana')->exists())->toBeTrue();

    $freshUser = $user->fresh();

    $this->actingAs($freshUser)
        ->get(route('statistics.show', ['playerId' => $freshUser->battletag]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('allHeroesStats.eliminations', 321)
            ->where('rolesStats.0.metrics.games_played', 42)
            ->where('heroStats.0.metrics.eliminations', 55)
            ->where('heroSpecificStats.0.key', 'biotic_grenade_kills'),
        );

    $overfastRequests = Http::recorded()
        ->filter(fn (array $pair): bool => str_starts_with($pair[0]->url(), 'https://overfast-api.tekrop.fr/'))
        ->count();

    expect($overfastRequests)->toBe(12);
});

test('a missing player raises a clear exception', function () {
    Http::fake(function (Request $request) {
        if (parse_url($request->url(), PHP_URL_PATH) === '/players/Player-1234') {
            return Http::response([], 404);
        }

        return Http::response([], 404);
    });

    expect(fn () => app(OverfastService::class)->playerByBattleTag('Player-1234'))
        ->toThrow(OverfastProfileNotFoundException::class, 'OverFast has no public statistics for this player');
});

test('a profile without stats is rejected as unavailable', function () {
    Http::fake(function (Request $request) {
        if (parse_url($request->url(), PHP_URL_PATH) === '/players/Player-1234') {
            return Http::response([
                'summary' => ['username' => 'Player'],
                'stats' => null,
            ]);
        }

        return Http::response([], 404);
    });

    expect(fn () => app(OverfastService::class)->playerByBattleTag('Player-1234'))
        ->toThrow(OverfastProfileNotFoundException::class, 'private or has no statistics');
});

test('BattleTag lookup normalizes the full OverFast profile response', function () {
    Http::fake(function (Request $request) {
        if (parse_url($request->url(), PHP_URL_PATH) === '/players/Player-1234') {
            return Http::response([
                'summary' => [
                    'username' => 'Player',
                    'title' => null,
                ],
                'stats' => [],
            ]);
        }

        return Http::response([], 404);
    });

    $player = app(OverfastService::class)->playerByBattleTag('Player-1234');

    expect($player['player_id'])->toBe('Player-1234')
        ->and($player['name'])->toBe('Player')
        ->and($player['is_public'])->toBeTrue();
});
