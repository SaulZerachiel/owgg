<?php

namespace App\Http\Controllers;

use App\Models\HeroStats;
use App\Models\PlayerStatSnapshot;
use App\Models\Rank;
use App\Models\RolesStats;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function show(Request $request): Response
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        $profile = $user->profiles()
            ->with([
                'allHeroesStats',
                'ranks.role',
                'rolesStats.role',
                'heroStats.hero.role',
            ])
            ->orderByDesc('updated_at')
            ->first();

        $snapshots = $profile?->statSnapshots()
            ->orderByDesc('captured_at')
            ->orderByDesc('id')
            ->limit(2)
            ->get();
        $currentSnapshot = $snapshots?->first();
        $previousSnapshot = $snapshots?->get(1);
        $progression = $currentSnapshot instanceof PlayerStatSnapshot
            && $previousSnapshot instanceof PlayerStatSnapshot
                ? $this->progression($currentSnapshot, $previousSnapshot)
                : null;

        $latestRanks = $profile?->ranks
            ->groupBy('role_id')
            ->map(static fn ($ranks): ?Rank => $ranks
                ->sortByDesc(static fn (Rank $rank): int => (int) $rank->season)
                ->first())
            ->filter()
            ->values()
            ->map(static fn (Rank $rank): array => [
                'role' => $rank->role->role_name,
                'season' => $rank->season,
                'tier' => $rank->tier,
                'division' => $rank->division,
            ]) ?? collect();

        $roles = $profile?->rolesStats
            ->map(static fn (RolesStats $stats): array => [
                'role' => $stats->role->role_name,
                'games_played' => $stats->games_played,
                'winrate' => $stats->winrate,
                'kda' => $stats->kda,
            ]) ?? collect();

        $topHeroes = $profile?->heroStats
            ->sortByDesc(static fn (HeroStats $stats): int => (int) $stats->time_played)
            ->take(3)
            ->values()
            ->map(static fn (HeroStats $stats): array => [
                'hero' => $stats->hero->hero_name,
                'role' => $stats->hero->role->role_name,
                'games_played' => $stats->games_played,
                'winrate' => $stats->win_percentage,
                'time_played' => $stats->time_played,
            ]) ?? collect();

        return Inertia::render('Dashboard', [
            'profile' => $profile?->only(['name', 'avatar', 'title', 'last_updated_at']),
            'summary' => $profile?->allHeroesStats
                ? Arr::only($profile->allHeroesStats->attributesToArray(), [
                    'games_played',
                    'games_won',
                    'games_lost',
                    'winrate',
                    'kda',
                    'time_played',
                    'deaths_avg_per_10_min',
                ])
                : null,
            'ranks' => $latestRanks,
            'roles' => $roles,
            'topHeroes' => $topHeroes,
            'progression' => $progression,
            'statsSyncedAt' => $user->stats_synced_at?->toIso8601String(),
            'statisticsUrl' => $user->battletag
                ? route('statistics.show', ['playerId' => $user->battletag])
                : null,
            'coachUrl' => route('chat'),
        ]);
    }

    /**
     * @return array{
     *     capturedAt: string,
     *     previousCapturedAt: string,
     *     winrate: array{current: float|null, previous: float|null, change: float|null},
     *     deathsPer10: array{current: float|null, previous: float|null, change: float|null},
     *     kda: array{current: float|null, previous: float|null, change: float|null},
     *     roles: array<int, array{name: string, comparisons: array<string, array{current: float|null, previous: float|null, change: float|null}>}>,
     *     heroes: array<int, array{name: string, comparisons: array<string, array{current: float|null, previous: float|null, change: float|null}>}>
     * }
     */
    private function progression(PlayerStatSnapshot $current, PlayerStatSnapshot $previous): array
    {
        $currentStats = $current->snapshot['all_heroes'] ?? [];
        $previousStats = $previous->snapshot['all_heroes'] ?? [];
        $currentSnapshot = $current->snapshot;
        $previousSnapshot = $previous->snapshot;

        return [
            'capturedAt' => $current->captured_at->toIso8601String(),
            'previousCapturedAt' => $previous->captured_at->toIso8601String(),
            'winrate' => $this->compare(
                $currentStats['winrate'] ?? null,
                $previousStats['winrate'] ?? null,
            ),
            'deathsPer10' => $this->compare(
                $currentStats['deaths_avg_per_10_min'] ?? null,
                $previousStats['deaths_avg_per_10_min'] ?? null,
            ),
            'kda' => $this->compare(
                $currentStats['kda'] ?? null,
                $previousStats['kda'] ?? null,
            ),
            'roles' => $this->compareGroups(
                $currentSnapshot['roles'] ?? [],
                $previousSnapshot['roles'] ?? [],
                'role',
                ['winrate' => 'winrate', 'kda' => 'kda'],
            ),
            'heroes' => $this->compareGroups(
                $currentSnapshot['heroes'] ?? [],
                $previousSnapshot['heroes'] ?? [],
                'hero',
                [
                    'winrate' => 'win_percentage',
                    'eliminations_avg_per_10_min' => 'eliminations_avg_per_10_min',
                    'deaths_avg_per_10_min' => 'deaths_avg_per_10_min',
                ],
                5,
            ),
        ];
    }

    /**
     * @param  array<string, string>  $metricKeys
     * @return array<int, array{name: string, comparisons: array<string, array{current: float|null, previous: float|null, change: float|null}>}>
     */
    private function compareGroups(
        mixed $currentGroups,
        mixed $previousGroups,
        string $groupKey,
        array $metricKeys,
        int $limit = 0,
    ): array {
        if (! is_array($currentGroups) || ! is_array($previousGroups)) {
            return [];
        }

        if ($limit > 0) {
            usort($currentGroups, static function (mixed $first, mixed $second): int {
                $firstTime = is_array($first)
                    && is_array($first['metrics'] ?? null)
                    && is_numeric($first['metrics']['time_played'] ?? null)
                    ? (float) $first['metrics']['time_played']
                    : 0;
                $secondTime = is_array($second)
                    && is_array($second['metrics'] ?? null)
                    && is_numeric($second['metrics']['time_played'] ?? null)
                    ? (float) $second['metrics']['time_played']
                    : 0;

                return $secondTime <=> $firstTime;
            });
            $currentGroups = array_slice($currentGroups, 0, $limit);
        }

        $previousByName = [];

        foreach ($previousGroups as $group) {
            if (is_array($group) && is_string($group[$groupKey] ?? null)) {
                $previousByName[$group[$groupKey]] = $group;
            }
        }

        $comparisons = [];

        foreach ($currentGroups as $group) {
            if (! is_array($group) || ! is_string($group[$groupKey] ?? null)) {
                continue;
            }

            $name = $group[$groupKey];
            $currentMetrics = is_array($group['metrics'] ?? null) ? $group['metrics'] : [];
            $previousMetrics = is_array($previousByName[$name]['metrics'] ?? null)
                ? $previousByName[$name]['metrics']
                : [];
            $metricComparisons = [];

            foreach ($metricKeys as $key => $metricKey) {
                $comparison = $this->compare(
                    $currentMetrics[$metricKey] ?? null,
                    $previousMetrics[$metricKey] ?? null,
                );

                if ($comparison['current'] !== null || $comparison['previous'] !== null) {
                    $metricComparisons[$key] = $comparison;
                }
            }

            if ($metricComparisons !== []) {
                $comparisons[] = [
                    'name' => $name,
                    'comparisons' => $metricComparisons,
                ];
            }
        }

        return $comparisons;
    }

    /**
     * @return array{current: float|null, previous: float|null, change: float|null}
     */
    private function compare(mixed $current, mixed $previous): array
    {
        $currentValue = is_numeric($current) ? (float) $current : null;
        $previousValue = is_numeric($previous) ? (float) $previous : null;

        return [
            'current' => $currentValue,
            'previous' => $previousValue,
            'change' => $currentValue !== null && $previousValue !== null
                ? round($currentValue - $previousValue, 2)
                : null,
        ];
    }
}
