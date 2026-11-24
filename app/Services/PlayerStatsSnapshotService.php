<?php

namespace App\Services;

use App\Models\HeroSpecificStat;
use App\Models\HeroStats;
use App\Models\Profile;
use App\Models\Rank;
use App\Models\RolesStats;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use JsonException;

final class PlayerStatsSnapshotService
{
    /**
     * Save a snapshot only when its statistics differ from the latest one.
     *
     * @throws JsonException
     */
    public function capture(Profile $profile): bool
    {
        $snapshot = $this->payload($profile);
        $fingerprint = hash('sha256', json_encode(
            $snapshot,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION,
        ));
        $latestFingerprint = $profile->statSnapshots()
            ->orderByDesc('captured_at')
            ->orderByDesc('id')
            ->value('fingerprint');

        if ($latestFingerprint === $fingerprint) {
            return false;
        }

        $profile->statSnapshots()->create([
            'fingerprint' => $fingerprint,
            'snapshot' => $snapshot,
            'captured_at' => now(),
        ]);

        return true;
    }

    /** @return array<string, mixed> */
    public function payload(Profile $profile): array
    {
        $ranks = $profile->ranks()
            ->with('role')
            ->orderBy('role_id')
            ->orderBy('season')
            ->get()
            ->map(static fn (Rank $rank): array => [
                'role' => $rank->role->role_name,
                'season' => $rank->season,
                'tier' => $rank->tier,
                'division' => $rank->division,
            ])
            ->all();

        $roles = $profile->rolesStats()
            ->with('role')
            ->orderBy('role_id')
            ->get()
            ->map(fn (RolesStats $stats): array => [
                'role' => $stats->role->role_name,
                'metrics' => $this->metrics($stats, ['roles_stats_id', 'role_id', 'player_id']),
            ])
            ->all();

        $allHeroes = $profile->allHeroesStats()->first();
        $heroes = $profile->heroStats()
            ->with(['hero.role'])
            ->orderBy('hero_id')
            ->get()
            ->map(fn (HeroStats $stats): array => [
                'hero' => $stats->hero->hero_name,
                'role' => $stats->hero->role->role_name,
                'metrics' => $this->metrics($stats, ['id', 'player_id', 'hero_id']),
            ])
            ->all();

        $heroSpecific = $profile->heroSpecificStats()
            ->with('hero')
            ->orderBy('hero_id')
            ->orderBy('stat_key')
            ->get()
            ->map(static fn (HeroSpecificStat $stat): array => [
                'hero' => $stat->hero->hero_name,
                'key' => $stat->stat_key,
                'value' => $stat->stat_value,
            ])
            ->all();

        return [
            'schema_version' => 1,
            'ranks' => $ranks,
            'roles' => $roles,
            'all_heroes' => $allHeroes
                ? $this->metrics($allHeroes, ['player_id'])
                : [],
            'heroes' => $heroes,
            'hero_specific' => $heroSpecific,
        ];
    }

    /**
     * @param  array<int, string>  $identifiers
     * @return array<string, mixed>
     */
    private function metrics(Model $model, array $identifiers): array
    {
        return Arr::except($model->attributesToArray(), [
            ...$identifiers,
            'created_at',
            'updated_at',
        ]);
    }
}
