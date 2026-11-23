<?php

use App\Models\AiCall;
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

uses(RefreshDatabase::class);

test('model factories create records with valid relationships', function () {
    $hero = Hero::factory()->create();
    $profile = Profile::factory()->create();

    $allHeroesStats = AllHeroesStats::factory()->create();
    $heroStats = HeroStats::factory()->create();
    $heroSpecificStat = HeroSpecificStat::factory()->create();
    $rank = Rank::factory()->create();
    $rolesStats = RolesStats::factory()->create();
    $aiCall = AiCall::factory()->create();

    expect($hero->role)->toBeInstanceOf(Role::class)
        ->and($profile->user)->toBeInstanceOf(User::class)
        ->and($allHeroesStats->profile)->toBeInstanceOf(Profile::class)
        ->and($heroStats->hero)->toBeInstanceOf(Hero::class)
        ->and($heroSpecificStat->hero)->toBeInstanceOf(Hero::class)
        ->and($rank->role)->toBeInstanceOf(Role::class)
        ->and($rolesStats->profile)->toBeInstanceOf(Profile::class)
        ->and($aiCall->user)->toBeInstanceOf(User::class);
});

test('database seeder is repeatable and seeds related domain data', function () {
    $this->seed();
    $this->seed();

    $profile = Profile::query()->where('player_id', 'demo-player')->firstOrFail();

    expect(Role::query()->count())->toBe(3)
        ->and(Hero::query()->count())->toBe(3)
        ->and($profile->allHeroesStats)->not->toBeNull()
        ->and($profile->ranks()->count())->toBe(3)
        ->and($profile->rolesStats()->count())->toBe(3)
        ->and($profile->heroStats()->count())->toBe(3)
        ->and($profile->heroSpecificStats()->count())->toBe(3);
});
