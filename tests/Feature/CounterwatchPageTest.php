<?php

use App\Models\User;
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
            ->where('heroes.0.guide.counters.tank.0.key', 'doomfist')
            ->where('heroes.0.guide.counters.tank.0.winrateDifference', 4)
            ->where('compositionGuide.cycle.0.beats', 'brawl')
            ->where('catalogError', null),
        );

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

test('map-specific winrates reorder matchup suggestions and the tier list', function () {
    Cache::flush();
    Http::fake(function (Request $request) {
        return match (parse_url($request->url(), PHP_URL_PATH)) {
            '/heroes' => Http::response([
                ['key' => 'ana', 'name' => 'Ana', 'role' => 'support', 'subrole' => 'tactician'],
                ['key' => 'winston', 'name' => 'Winston', 'role' => 'tank'],
                ['key' => 'dva', 'name' => 'D.Va', 'role' => 'tank'],
                ['key' => 'doomfist', 'name' => 'Doomfist', 'role' => 'tank'],
            ]),
            '/maps' => Http::response([
                ['key' => 'circuit-royal', 'name' => 'Circuit Royal', 'uses' => ['standard']],
            ]),
            '/heroes/stats' => Http::response([
                ['hero' => 'dva', 'winrate' => 55.0, 'pickrate' => 8.0],
                ['hero' => 'doomfist', 'winrate' => 53.0, 'pickrate' => 6.0],
                ['hero' => 'ana', 'winrate' => 50.0, 'pickrate' => 20.0],
                ['hero' => 'winston', 'winrate' => 48.0, 'pickrate' => 7.0],
            ]),
            default => Http::response([], 404),
        };
    });

    $this->actingAs(User::factory()->create())
        ->get(route('counterwatch', ['map' => 'circuit-royal', 'hero' => 'ana']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('selectedMapKey', 'circuit-royal')
            ->where('selectedMapName', 'Circuit Royal')
            ->where('selectedHeroKey', 'ana')
            ->where('tierList.0.key', 'dva')
            ->where('heroes.0.guide.counters.tank.0.key', 'dva')
            ->where('heroes.0.guide.counters.tank.0.winrateDifference', 5),
        );

    Http::assertSent(fn (Request $request): bool => str_contains(
        $request->url(),
        '&map=circuit-royal',
    ));
});
