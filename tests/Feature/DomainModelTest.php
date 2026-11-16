<?php

use App\Models\Hero;
use App\Models\MatchAnalysis;
use App\Models\Profile;
use App\Models\User;

test('a player profile owns matches and their analysis recommendations', function () {
    $profile = Profile::factory()->create();
    $hero = Hero::create(['slug' => 'ana', 'name' => 'Ana', 'role' => 'support']);
    $match = $profile->matches()->create([
        'mode' => 'competitive',
        'won' => true,
        'played_at' => now(),
    ]);

    $match->heroStats()->create(['hero_id' => $hero->id]);
    $analysis = $match->analysis()->create([
        'summary' => 'Summary',
        'strengths' => ['healing'],
        'weaknesses' => ['aim'],
    ]);
    $analysis->recommendations()->create([
        'title' => 'Improve aim',
        'content' => 'Practice your aim.',
    ]);

    expect($profile->matches)->toHaveCount(1)
        ->and($match->heroStats)->toHaveCount(1)
        ->and($match->analysis)->toBeInstanceOf(MatchAnalysis::class)
        ->and($analysis->recommendations)->toHaveCount(1);
});

test('a profile belongs to its user', function () {
    $user = User::factory()->create();
    $profile = Profile::factory()->create(['user_id' => $user->id]);

    expect($profile->user->is($user))->toBeTrue();
});
