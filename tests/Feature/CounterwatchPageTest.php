<?php

use App\Models\User;
use App\Services\CounterwatchGuide;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('guests are redirected from Counterwatch', function () {
    $this->get(route('counterwatch'))
        ->assertRedirect(route('login'));
});

test('authenticated users can browse heroes and role-specific matchup suggestions', function () {
    Cache::flush();
    Http::fake(function (Request $request) {
        return match (parse_url($request->url(), PHP_URL_PATH)) {
            '/heroes' => Http::response([
                ['key' => 'ana', 'name' => 'Ana', 'role' => 'support', 'subrole' => 'tactician'],
                ['key' => 'winston', 'name' => 'Winston', 'role' => 'tank'],
                ['key' => 'dva', 'name' => 'D.Va', 'role' => 'tank'],
                ['key' => 'doomfist', 'name' => 'Doomfist', 'role' => 'tank'],
                ['key' => 'tracer', 'name' => 'Tracer', 'role' => 'damage'],
                ['key' => 'venture', 'name' => 'Venture', 'role' => 'damage'],
                ['key' => 'genji', 'name' => 'Genji', 'role' => 'damage'],
                ['key' => 'brigitte', 'name' => 'Brigitte', 'role' => 'support'],
                ['key' => 'kiriko', 'name' => 'Kiriko', 'role' => 'support'],
                ['key' => 'lucio', 'name' => 'Lúcio', 'role' => 'support'],
            ]),
            '/maps' => Http::response([
                ['key' => 'circuit-royal', 'name' => 'Circuit Royal', 'uses' => ['standard']],
                ['key' => 'havana', 'name' => 'Havana', 'uses' => ['standard']],
                ['key' => 'junkertown', 'name' => 'Junkertown', 'uses' => ['standard']],
                ['key' => 'kings-row', 'name' => 'King’s Row', 'uses' => ['standard']],
                ['key' => 'lijiang-tower', 'name' => 'Lijiang Tower', 'uses' => ['standard']],
                ['key' => 'samoa', 'name' => 'Samoa', 'uses' => ['standard']],
            ]),
            '/heroes/stats' => Http::response([
                ['hero' => 'doomfist', 'winrate' => 54.0, 'pickrate' => 5.0],
                ['hero' => 'winston', 'winrate' => 52.0, 'pickrate' => 6.0],
                ['hero' => 'ana', 'winrate' => 50.0, 'pickrate' => 25.0],
                ['hero' => 'tracer', 'winrate' => 49.0, 'pickrate' => 8.0],
                ['hero' => 'venture', 'winrate' => 48.0, 'pickrate' => 3.0],
                ['hero' => 'genji', 'winrate' => 47.0, 'pickrate' => 12.0],
                ['hero' => 'dva', 'winrate' => 46.0, 'pickrate' => 4.0],
                ['hero' => 'brigitte', 'winrate' => 51.0, 'pickrate' => 5.0],
                ['hero' => 'kiriko', 'winrate' => 50.5, 'pickrate' => 20.0],
                ['hero' => 'lucio', 'winrate' => 48.5, 'pickrate' => 3.0],
            ]),
            default => Http::response([], 404),
        };
    });

    $this->actingAs(User::factory()->create())
        ->get(route('counterwatch'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Counterwatch/Index')
            ->has('heroes', 10)
            ->where('heroes.0.key', 'ana')
            ->where('heroes.0.guide.styles.0.label', 'Poke')
            ->has('heroes.0.guide.counters.tank', 3)
            ->has('heroes.0.guide.counters.damage', 3)
            ->has('heroes.0.guide.counters.support', 3)
            ->has('heroes.0.guide.maps.favorable', 3)
            ->has('heroes.0.guide.maps.difficult', 3)
            ->where('tierList.0.key', 'doomfist')
            ->where('tierList.0.winrate', 54)
            ->where('heroes.0.guide.counters.tank.0.key', 'winston')
            ->where('heroes.0.guide.counters.tank.0.winrateDifference', 2)
            ->where('heroes.0.guide.counters.damage.0.key', 'tracer')
            ->where('heroes.0.guide.counters.support.0.key', 'kiriko')
            ->missing('heroes.0.guide.counters.tank.0.score')
            ->where('compositionGuide.cycle.0.beats', 'brawl')
            ->where('catalogError', null),
        );

    $this->assertDatabaseHas('hero_matchups', [
        'hero_id' => 'winston',
        'target_hero_id' => 'bastion',
        'relation_type' => 'counter',
        'score' => -2,
    ]);
    $this->assertDatabaseHas('hero_matchups', [
        'hero_id' => 'winston',
        'target_hero_id' => 'tracer',
        'relation_type' => 'synergy',
        'score' => 2,
    ]);

    Http::assertSent(fn (Request $request): bool => str_ends_with(
        $request->url(),
        '/heroes?locale=fr-fr',
    ));
    Http::assertSent(fn (Request $request): bool => str_ends_with(
        $request->url(),
        '/maps',
    ));
    Http::assertSent(fn (Request $request): bool => str_contains(
        $request->url(),
        '/heroes/stats?platform=pc&gamemode=competitive&region=europe&order_by=winrate%3Adesc',
    ));
});

test('every curated hero receives three suggestions for every role', function () {
    $guide = app(CounterwatchGuide::class);
    $archetypeMatchups = $guide->archetypeMatchups();
    $rosterByKey = [];

    foreach ($archetypeMatchups as $matchup) {
        $rosterByKey[$matchup['counter_hero_key']] = [
            'key' => $matchup['counter_hero_key'],
            'name' => $matchup['counter_hero_key'],
            'role' => $matchup['role'],
        ];
    }

    $profiles = (new ReflectionClass(CounterwatchGuide::class))
        ->getReflectionConstant('HERO_PROFILES')
        ->getValue();

    foreach (array_keys($profiles) as $heroKey) {
        $guideData = $guide->heroGuide(
            ['key' => $heroKey, 'name' => $heroKey, 'role' => 'damage'],
            array_values($rosterByKey),
            matchups: $guide->matchups(),
            archetypeMatchups: $archetypeMatchups,
        );

        foreach (['tank', 'damage', 'support'] as $role) {
            $this->assertCount(
                3,
                $guideData['counters'][$role],
                "Expected three {$role} suggestions for {$heroKey}.",
            );
        }
    }
});

test('the matchup guide reports when the hero roster cannot be loaded', function () {
    Cache::flush();
    Http::fake(fn () => Http::response([], 503));

    $this->actingAs(User::factory()->create())
        ->get(route('counterwatch'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Counterwatch/Index')
            ->has('heroes', 0)
            ->where('catalogError', 'Le roster OverFast est momentanément indisponible. Réessaie dans quelques instants.')
            ->where('mapCatalogError', 'Les données des cartes sont momentanément indisponibles.')
            ->has('compositionGuide.cycle', 3),
        );
});

test('heroes remain available when the map catalog cannot be loaded', function () {
    Cache::flush();
    Http::fake(function (Request $request) {
        return match (parse_url($request->url(), PHP_URL_PATH)) {
            '/heroes' => Http::response([
                ['key' => 'ana', 'name' => 'Ana', 'role' => 'support', 'subrole' => 'tactician'],
            ]),
            '/maps' => Http::response([], 503),
            '/heroes/stats' => Http::response([
                ['hero' => 'ana', 'winrate' => 50.0, 'pickrate' => 20.0],
            ]),
            default => Http::response([], 404),
        };
    });

    $this->actingAs(User::factory()->create())
        ->get(route('counterwatch'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Counterwatch/Index')
            ->has('heroes', 1)
            ->where('catalogError', null)
            ->where('mapCatalogError', 'Les données des cartes sont momentanément indisponibles.')
            ->where('heroStatsError', null)
            ->has('tierList', 1)
            ->has('heroes.0.guide.maps.favorable', 0)
            ->has('heroes.0.guide.maps.difficult', 0),
        );
});

test('the hero guide remains available when OverFast statistics fail', function () {
    Cache::flush();
    Http::fake(function (Request $request) {
        return match (parse_url($request->url(), PHP_URL_PATH)) {
            '/heroes' => Http::response([
                ['key' => 'ana', 'name' => 'Ana', 'role' => 'support', 'subrole' => 'tactician'],
            ]),
            '/maps' => Http::response([
                ['key' => 'circuit-royal', 'name' => 'Circuit Royal', 'uses' => ['standard']],
            ]),
            '/heroes/stats' => Http::response([], 503),
            default => Http::response([], 404),
        };
    });

    $this->actingAs(User::factory()->create())
        ->get(route('counterwatch'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('heroes', 1)
            ->where('heroStatsError', 'Les statistiques OverFast sont momentanément indisponibles.')
            ->has('tierList', 0),
        );
});

test('bastion matchup suggestions do not mistake negative relations for counters', function () {
    Cache::flush();
    Http::fake(function (Request $request) {
        return match (parse_url($request->url(), PHP_URL_PATH)) {
            '/heroes' => Http::response([
                ['key' => 'bastion', 'name' => 'Bastion', 'role' => 'damage'],
                ['key' => 'winston', 'name' => 'Winston', 'role' => 'tank'],
                ['key' => 'sigma', 'name' => 'Sigma', 'role' => 'tank'],
                ['key' => 'dva', 'name' => 'D.Va', 'role' => 'tank'],
                ['key' => 'orisa', 'name' => 'Orisa', 'role' => 'tank'],
                ['key' => 'genji', 'name' => 'Genji', 'role' => 'damage'],
                ['key' => 'pharah', 'name' => 'Pharah', 'role' => 'damage'],
                ['key' => 'echo', 'name' => 'Echo', 'role' => 'damage'],
                ['key' => 'ana', 'name' => 'Ana', 'role' => 'support'],
                ['key' => 'zenyatta', 'name' => 'Zenyatta', 'role' => 'support'],
                ['key' => 'baptiste', 'name' => 'Baptiste', 'role' => 'support'],
            ]),
            '/maps' => Http::response([]),
            '/heroes/stats' => Http::response([
                ['hero' => 'bastion', 'winrate' => 50.0, 'pickrate' => 8.0],
                ['hero' => 'winston', 'winrate' => 52.0, 'pickrate' => 6.0],
                ['hero' => 'sigma', 'winrate' => 51.0, 'pickrate' => 5.0],
                ['hero' => 'dva', 'winrate' => 49.0, 'pickrate' => 5.0],
                ['hero' => 'orisa', 'winrate' => 48.0, 'pickrate' => 5.0],
                ['hero' => 'genji', 'winrate' => 49.0, 'pickrate' => 12.0],
                ['hero' => 'pharah', 'winrate' => 48.0, 'pickrate' => 5.0],
                ['hero' => 'echo', 'winrate' => 47.0, 'pickrate' => 5.0],
                ['hero' => 'ana', 'winrate' => 50.0, 'pickrate' => 20.0],
                ['hero' => 'zenyatta', 'winrate' => 48.0, 'pickrate' => 5.0],
                ['hero' => 'baptiste', 'winrate' => 47.0, 'pickrate' => 5.0],
            ]),
            default => Http::response([], 404),
        };
    });

    $this->actingAs(User::factory()->create())
        ->get(route('counterwatch', ['hero' => 'bastion']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('selectedHeroKey', 'bastion')
            ->has('heroes.0.guide.counters.tank', 3)
            ->where('heroes.0.guide.counters.tank.0.key', 'sigma')
            ->where('heroes.0.guide.counters.tank.1.key', 'dva')
            ->where('heroes.0.guide.counters.tank.2.key', 'orisa')
            ->has('heroes.0.guide.counters.damage', 3)
            ->where('heroes.0.guide.counters.damage.0.key', 'genji')
            ->has('heroes.0.guide.counters.support', 3)
            ->where('heroes.0.guide.counters.support.0.key', 'ana')
            ->missing('heroes.0.guide.counters.tank.0.score'),
        );
});

test('map-specific winrates break ties between equally rated matchup suggestions', function () {
    Cache::flush();
    Http::fake(function (Request $request) {
        return match (parse_url($request->url(), PHP_URL_PATH)) {
            '/heroes' => Http::response([
                ['key' => 'pharah', 'name' => 'Pharah', 'role' => 'damage'],
                ['key' => 'soldier-76', 'name' => 'Soldier: 76', 'role' => 'damage'],
                ['key' => 'cassidy', 'name' => 'Cassidy', 'role' => 'damage'],
                ['key' => 'ashe', 'name' => 'Ashe', 'role' => 'damage'],
                ['key' => 'widowmaker', 'name' => 'Widowmaker', 'role' => 'damage'],
            ]),
            '/maps' => Http::response([
                ['key' => 'circuit-royal', 'name' => 'Circuit Royal', 'uses' => ['standard']],
            ]),
            '/heroes/stats' => Http::response([
                ['hero' => 'cassidy', 'winrate' => 57.0, 'pickrate' => 5.0],
                ['hero' => 'widowmaker', 'winrate' => 56.0, 'pickrate' => 20.0],
                ['hero' => 'soldier-76', 'winrate' => 55.0, 'pickrate' => 6.0],
                ['hero' => 'ashe', 'winrate' => 53.0, 'pickrate' => 12.0],
                ['hero' => 'pharah', 'winrate' => 50.0, 'pickrate' => 8.0],
            ]),
            default => Http::response([], 404),
        };
    });

    $this->actingAs(User::factory()->create())
        ->get(route('counterwatch', ['map' => 'circuit-royal', 'hero' => 'pharah']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('selectedMapKey', 'circuit-royal')
            ->where('selectedMapName', 'Circuit Royal')
            ->where('selectedHeroKey', 'pharah')
            ->where('tierList.0.key', 'cassidy')
            ->where('heroes.0.guide.counters.damage.0.key', 'cassidy')
            ->where('heroes.0.guide.counters.damage.0.winrateDifference', 7)
            ->where('heroes.0.guide.counters.damage.1.key', 'widowmaker')
            ->where('heroes.0.guide.counters.damage.2.key', 'soldier-76'),
        );

    Http::assertSent(fn (Request $request): bool => str_contains(
        $request->url(),
        '&map=circuit-royal',
    ));
});
