<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;

uses(RefreshDatabase::class);

test('Battle.net login stores the OverFast-formatted BattleTag', function () {
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
});
