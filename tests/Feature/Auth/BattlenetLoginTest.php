<?php

use App\Jobs\ImportPlayerStats;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;

uses(RefreshDatabase::class);

test('Battle.net login stores the OverFast-formatted BattleTag', function () {
    Queue::fake();

    $battlenetUser = Mockery::mock();
    $battlenetUser->shouldReceive('getId')->twice()->andReturn('battlenet-user-id');
    $battlenetUser->shouldReceive('getNickname')->once()->andReturn('Player#1234');
    $battlenetUser->shouldReceive('getEmail')->once()->andReturnNull();

    $driver = Mockery::mock();
    $driver->shouldReceive('user')->once()->andReturn($battlenetUser);

    Socialite::shouldReceive('driver')
        ->once()
        ->with('battlenet')
        ->andReturn($driver);

    $this->get(route('battlenet.callback'))->assertRedirect(route('dashboard'));

    expect(User::query()->sole()->battletag)->toBe('Player-1234');
    Queue::assertPushed(ImportPlayerStats::class, 1);
});

test('Battle.net login skips an import synced within the last hour', function () {
    Queue::fake();

    User::factory()->create([
        'battlenet_id' => 'battlenet-user-id',
        'stats_synced_at' => now()->subMinutes(30),
    ]);

    $battlenetUser = Mockery::mock();
    $battlenetUser->shouldReceive('getId')->twice()->andReturn('battlenet-user-id');
    $battlenetUser->shouldReceive('getNickname')->once()->andReturn('Player#1234');
    $battlenetUser->shouldReceive('getEmail')->once()->andReturnNull();

    $driver = Mockery::mock();
    $driver->shouldReceive('user')->once()->andReturn($battlenetUser);

    Socialite::shouldReceive('driver')
        ->once()
        ->with('battlenet')
        ->andReturn($driver);

    $this->get(route('battlenet.callback'))->assertRedirect(route('dashboard'));

    Queue::assertNothingPushed();
});

test('a private OverFast profile shows an error toast after Battle.net login', function () {
    config(['queue.default' => 'sync']);

    Http::fake([
        '*players*' => Http::response([
            'total' => 0,
            'results' => [],
        ]),
    ]);

    $battlenetUser = Mockery::mock();
    $battlenetUser->shouldReceive('getId')->twice()->andReturn('battlenet-user-id');
    $battlenetUser->shouldReceive('getNickname')->once()->andReturn('Player#1234');
    $battlenetUser->shouldReceive('getEmail')->once()->andReturnNull();

    $driver = Mockery::mock();
    $driver->shouldReceive('user')->once()->andReturn($battlenetUser);

    Socialite::shouldReceive('driver')
        ->once()
        ->with('battlenet')
        ->andReturn($driver);

    $this->get(route('battlenet.callback'))
        ->assertRedirect(route('dashboard'))
        ->assertInertiaFlash('toast', [
            'type' => 'error',
            'message' => 'Your Overwatch profile is private or unavailable on OverFast.',
        ]);
});
