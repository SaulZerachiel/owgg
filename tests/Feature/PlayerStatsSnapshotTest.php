<?php

use App\Models\AllHeroesStats;
use App\Models\PlayerStatSnapshot;
use App\Models\Profile;
use App\Services\PlayerStatsSnapshotService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('statistics snapshots are stored only when the imported values change', function () {
    $profile = Profile::factory()->create();
    $stats = AllHeroesStats::factory()->create(['player_id' => $profile->player_id]);
    $snapshots = app(PlayerStatsSnapshotService::class);

    expect($snapshots->capture($profile))->toBeTrue()
        ->and($snapshots->capture($profile->fresh()))->toBeFalse()
        ->and(PlayerStatSnapshot::query()->count())->toBe(1);

    $stats->games_played++;
    $stats->save();

    expect($snapshots->capture($profile->fresh()))->toBeTrue()
        ->and(PlayerStatSnapshot::query()->count())->toBe(2)
        ->and(PlayerStatSnapshot::query()->orderByDesc('id')->firstOrFail()->snapshot['all_heroes']['games_played'])
        ->toBe($stats->games_played);
});
