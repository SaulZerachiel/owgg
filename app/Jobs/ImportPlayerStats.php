<?php

namespace App\Jobs;

use App\Models\AllHeroesStats;
use App\Models\Hero;
use App\Models\HeroSpecificStat;
use App\Models\HeroStats;
use App\Models\Profile;
use App\Models\Rank;
use App\Models\Role;
use App\Models\RolesStats;
use App\Models\User;
use App\Services\OverfastService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;
use UnexpectedValueException;

class ImportPlayerStats implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 300;

    public int $uniqueFor = 3600;

    /** @var array<int, int> */
    public array $backoff = [30, 120];

    public function __construct(public User $user) {}

    public function uniqueId(): string
    {
        return (string) $this->user->getKey();
    }

    public function handle(OverfastService $overfast): void
    {
        $player = $overfast->playerByBattleTag($this->user->battletag);
        $playerId = $player['player_id'];
        $summary = $overfast->playerSummary($playerId);
        $statsSummary = $overfast->playerStatsSummary($playerId);
        $careerStats = $overfast->playerCareerStats($playerId);
        $roles = $overfast->roles();
        $heroes = $overfast->heroes();

        $roleRows = $this->roleRows($roles);
        $roleIds = array_column($roleRows, 'role_id');
        $heroRows = $this->heroRows($heroes, $roleIds);
        $heroIds = array_column($heroRows, 'hero_id');

        DB::transaction(function () use ($playerId, $player, $summary, $statsSummary, $careerStats, $roleRows, $heroRows, $roleIds, $heroIds): void {
            Role::upsert($roleRows, ['role_id'], ['role_name', 'role_icon', 'updated_at']);
            Hero::upsert($heroRows, ['hero_id'], ['role_id', 'hero_name', 'updated_at']);

            Profile::query()->updateOrCreate(
                ['player_id' => $playerId],
                [
                    'user_id' => $this->user->id,
                    'name' => $player['name'],
                    'avatar' => $player['avatar'] ?? null,
                    'namecard' => $player['namecard'] ?? null,
                    'title' => $player['title'] ?? null,
                    'career_url' => $player['career_url'],
                    'blizzard_id' => $player['blizzard_id'],
                    'last_updated_at' => isset($player['last_updated_at'])
                        ? Carbon::createFromTimestampUTC((int) $player['last_updated_at'])
                        : null,
                    'is_public' => $player['is_public'],
                ],
            );

            $this->upsertGrouped(
                $this->rankRows($playerId, $summary, $roleIds),
                ['player_id', 'role_id', 'season'],
                ['rank_id', 'player_id', 'role_id', 'season'],
                Rank::class,
            );

            $this->upsertGrouped(
                $this->roleStatsRows($playerId, $statsSummary, $roleIds),
                ['role_id', 'player_id'],
                ['roles_stats_id', 'role_id', 'player_id'],
                RolesStats::class,
            );

            $allHeroesMetrics = array_merge(
                $this->summaryMetrics($statsSummary['general'] ?? [], new AllHeroesStats),
                $this->careerMetrics($careerStats['all-heroes'] ?? [], new AllHeroesStats),
            );

            AllHeroesStats::query()->updateOrCreate(
                ['player_id' => $playerId],
                $allHeroesMetrics,
            );

            $this->upsertGrouped(
                $this->heroStatsRows($playerId, $statsSummary, $careerStats, $heroIds),
                ['player_id', 'hero_id'],
                ['player_id', 'hero_id'],
                HeroStats::class,
            );

            $this->upsertGrouped(
                $this->heroSpecificStatRows($playerId, $careerStats, $heroIds),
                ['hero_id', 'player_id', 'stat_key'],
                ['hero_id', 'player_id', 'stat_key'],
                HeroSpecificStat::class,
            );

            $this->user->forceFill(['stats_synced_at' => now()])->save();
        });
    }

    /**
     * @param  array<int, mixed>  $roles
     * @return array<int, array{role_id: string, role_name: string, role_icon: string|null}>
     */
    private function roleRows(array $roles): array
    {
        $rows = [];

        foreach ($roles as $role) {
            if (! is_array($role) || ! is_string($role['key'] ?? null) || ! is_string($role['name'] ?? null)) {
                throw new UnexpectedValueException('OverFast returned an invalid role.');
            }

            $rows[] = [
                'role_id' => $role['key'],
                'role_name' => $role['name'],
                'role_icon' => $role['icon'] ?? null,
            ];
        }

        return $rows;
    }

    /**
     * @param  array<int, mixed>  $heroes
     * @param  array<int, string>  $roleIds
     * @return array<int, array{hero_id: string, role_id: string, hero_name: string}>
     */
    private function heroRows(array $heroes, array $roleIds): array
    {
        $rows = [];

        foreach ($heroes as $hero) {
            if (! is_array($hero)
                || ! is_string($hero['key'] ?? null)
                || ! is_string($hero['name'] ?? null)
                || ! is_string($hero['role'] ?? null)
                || ! in_array($hero['role'], $roleIds, true)) {
                throw new UnexpectedValueException('OverFast returned an invalid hero.');
            }

            $rows[] = [
                'hero_id' => $hero['key'],
                'role_id' => $hero['role'],
                'hero_name' => $hero['name'],
            ];
        }

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $summary
     * @param  array<int, string>  $roleIds
     * @return array<int, array<string, mixed>>
     */
    private function rankRows(string $playerId, array $summary, array $roleIds): array
    {
        $pcRanks = $summary['competitive']['pc'] ?? null;

        if (! is_array($pcRanks) || ! is_numeric($pcRanks['season'] ?? null)) {
            return [];
        }

        $rows = [];

        foreach (['tank', 'damage', 'support', 'open'] as $roleId) {
            $rank = $pcRanks[$roleId] ?? null;

            if (! is_array($rank) || ! in_array($roleId, $roleIds, true)) {
                continue;
            }

            $rows[] = [
                'rank_id' => (string) Str::uuid(),
                'player_id' => $playerId,
                'role_id' => $roleId,
                'season' => (string) $pcRanks['season'],
                'tier' => $rank['division'] ?? null,
                'division' => $rank['tier'] ?? null,
            ];
        }

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $summary
     * @param  array<int, string>  $roleIds
     * @return array<int, array<string, mixed>>
     */
    private function roleStatsRows(string $playerId, array $summary, array $roleIds): array
    {
        $roles = $summary['roles'] ?? [];

        if (! is_array($roles)) {
            throw new UnexpectedValueException('OverFast returned invalid role statistics.');
        }

        $rows = [];

        foreach ($roles as $roleId => $stats) {
            if (! is_array($stats) || ! in_array($roleId, $roleIds, true)) {
                throw new UnexpectedValueException('OverFast returned statistics for an unknown role.');
            }

            $rows[] = [
                'roles_stats_id' => (string) Str::uuid(),
                'role_id' => $roleId,
                'player_id' => $playerId,
                ...$this->summaryMetrics($stats, new RolesStats),
            ];
        }

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $summary
     * @param  array<string, mixed>  $careerStats
     * @param  array<int, string>  $heroIds
     * @return array<int, array<string, mixed>>
     */
    private function heroStatsRows(string $playerId, array $summary, array $careerStats, array $heroIds): array
    {
        $summaryHeroes = $summary['heroes'] ?? [];

        if (! is_array($summaryHeroes)) {
            throw new UnexpectedValueException('OverFast returned invalid hero statistics.');
        }

        $heroKeys = array_unique([
            ...array_keys($summaryHeroes),
            ...array_diff(array_keys($careerStats), ['all-heroes']),
        ]);
        $rows = [];

        foreach ($heroKeys as $heroId) {
            if (! in_array($heroId, $heroIds, true)) {
                throw new UnexpectedValueException("OverFast returned statistics for unknown hero [{$heroId}].");
            }

            $summaryMetrics = $this->summaryMetrics(
                is_array($summaryHeroes[$heroId] ?? null) ? $summaryHeroes[$heroId] : [],
                new HeroStats,
            );
            $careerMetrics = $this->careerMetrics(
                is_array($careerStats[$heroId] ?? null) ? $careerStats[$heroId] : [],
                new HeroStats,
            );

            $rows[] = [
                'player_id' => $playerId,
                'hero_id' => $heroId,
                ...array_merge($summaryMetrics, $careerMetrics),
            ];
        }

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $careerStats
     * @param  array<int, string>  $heroIds
     * @return array<int, array{hero_id: string, player_id: string, stat_key: string, stat_value: string|null}>
     */
    private function heroSpecificStatRows(string $playerId, array $careerStats, array $heroIds): array
    {
        $rows = [];

        foreach ($careerStats as $heroId => $groups) {
            if ($heroId === 'all-heroes' || ! is_array($groups)) {
                continue;
            }

            if (! in_array($heroId, $heroIds, true)) {
                throw new UnexpectedValueException("OverFast returned statistics for unknown hero [{$heroId}].");
            }

            foreach (($groups['hero_specific'] ?? []) as $key => $value) {
                if (! is_string($key) || (! is_scalar($value) && $value !== null)) {
                    throw new UnexpectedValueException('OverFast returned an invalid hero-specific statistic.');
                }

                $rows[] = [
                    'hero_id' => $heroId,
                    'player_id' => $playerId,
                    'stat_key' => $key,
                    'stat_value' => $value === null ? null : (string) $value,
                ];
            }
        }

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $summary
     * @return array<string, mixed>
     */
    private function summaryMetrics(array $summary, Model $model): array
    {
        $fillable = $model->getFillable();
        $metrics = [];

        foreach (['games_played', 'games_won', 'games_lost', 'time_played', 'kda'] as $key) {
            if (array_key_exists($key, $summary) && in_array($key, $fillable, true)) {
                $metrics[$key] = $summary[$key];
            }
        }

        if (array_key_exists('winrate', $summary)) {
            $this->addMetric($metrics, $fillable, ['winrate', 'win_percentage'], $summary['winrate']);
        }

        foreach (['total' => 'total', 'average' => 'average'] as $group => $prefix) {
            $values = $summary[$group] ?? null;

            if (! is_array($values)) {
                continue;
            }

            foreach ([
                'eliminations' => ['eliminations'],
                'assists' => ['assists'],
                'deaths' => ['deaths'],
                'damage' => ['damage', 'all_damage_done'],
                'healing' => ['healing', 'healing_done'],
            ] as $key => $aliases) {
                if (! array_key_exists($key, $values)) {
                    continue;
                }

                $candidates = array_map(
                    fn (string $alias): string => $prefix === 'total'
                        ? 'total_'.$alias
                        : 'average_'.$alias,
                    $aliases,
                );

                if ($prefix === 'average') {
                    $candidates = array_map(
                        fn (string $candidate): string => match ($candidate) {
                            'average_all_damage_done' => 'all_damage_done_avg_per_10_min',
                            'average_healing_done' => 'healing_done_avg_per_10_min',
                            'average_eliminations' => 'eliminations_avg_per_10_min',
                            'average_assists' => 'assists_avg_per_10_min',
                            'average_deaths' => 'deaths_avg_per_10_min',
                            default => $candidate,
                        },
                        $candidates,
                    );
                } elseif ($prefix === 'total') {
                    $candidates = array_map(
                        fn (string $candidate): string => match ($candidate) {
                            'total_all_damage_done' => 'all_damage_done',
                            'total_healing_done' => 'healing_done',
                            'total_eliminations' => 'eliminations',
                            'total_assists' => 'assists',
                            'total_deaths' => 'deaths',
                            default => $candidate,
                        },
                        $candidates,
                    );
                }

                $this->addMetric($metrics, $fillable, $candidates, $values[$key]);
            }
        }

        return $metrics;
    }

    /**
     * @param  array<string, mixed>  $groups
     * @return array<string, mixed>
     */
    private function careerMetrics(array $groups, Model $model): array
    {
        $fillable = $model->getFillable();
        $metrics = [];

        foreach ($groups as $stats) {
            if (! is_array($stats)) {
                continue;
            }

            foreach ($stats as $key => $value) {
                if (is_string($key)
                    && in_array($key, $fillable, true)
                    && (is_scalar($value) || $value === null)) {
                    $metrics[$key] = $value;
                }
            }
        }

        return $metrics;
    }

    /**
     * @param  array<string, mixed>  $metrics
     * @param  array<int, string>  $fillable
     * @param  array<int, string>  $candidates
     */
    private function addMetric(array &$metrics, array $fillable, array $candidates, mixed $value): void
    {
        foreach ($candidates as $candidate) {
            if (in_array($candidate, $fillable, true)) {
                $metrics[$candidate] = $value;

                return;
            }
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<int, string>  $uniqueBy
     * @param  array<int, string>  $excludedFromUpdate
     * @param  class-string<Model>  $modelClass
     */
    private function upsertGrouped(array $rows, array $uniqueBy, array $excludedFromUpdate, string $modelClass): void
    {
        $groups = [];

        foreach ($rows as $row) {
            $columns = array_keys($row);
            $signatureColumns = $columns;
            sort($signatureColumns);
            $groups[implode('|', $signatureColumns)][] = $row;
        }

        foreach ($groups as $group) {
            $columns = array_keys($group[0]);
            $normalized = array_map(
                fn (array $row): array => array_replace(array_fill_keys($columns, null), $row),
                $group,
            );
            $updateColumns = array_values(array_diff($columns, $excludedFromUpdate));

            $modelClass::upsert($normalized, $uniqueBy, $updateColumns);
        }
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('OverFast player stats import failed.', [
            'user_id' => $this->user->id,
            'exception' => $exception,
        ]);
    }
}
