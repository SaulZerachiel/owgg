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
    Http::fake(fn (Request $request) => str_ends_with(
        (string) parse_url($request->url(), PHP_URL_PATH),
        '/heroes',
    ) ? Http::response([
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
    ]) : Http::response([], 404));

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
            ->where('compositionGuide.cycle.0.beats', 'brawl')
            ->where('catalogError', null),
        );

    Http::assertSent(fn (Request $request): bool => str_ends_with(
        $request->url(),
        '/heroes?locale=fr-fr',
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
            ->has('compositionGuide.cycle', 3),
        );
});
