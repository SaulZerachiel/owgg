<?php

namespace App\Http\Controllers;

use App\Jobs\ImportPlayerStats;
use App\Models\HeroSpecificStat;
use App\Models\HeroStats;
use App\Models\Profile;
use App\Models\Rank;
use App\Models\RolesStats;
use App\Models\User;
use App\Services\OverfastProfileNotFoundException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use UnexpectedValueException;

class StatisticsController extends Controller
{
    public function show(Request $request, string $playerId): Response
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->battletag === $playerId, 404);

        $loadProfile = fn () => Profile::query()
            ->where('player_id', $playerId)
            ->where('user_id', $user->id)
            ->with([
                'allHeroesStats',
                'ranks.role',
                'rolesStats.role',
                'heroStats.hero',
                'heroSpecificStats.hero',
            ])
            ->first();

        $profile = $loadProfile();
        $needsSync = $profile?->allHeroesStats === null
            || $user->stats_synced_at === null
            || $user->stats_synced_at->lt(now()->subHour());
        $syncFailed = false;

        if ($needsSync) {
            try {
                ImportPlayerStats::dispatch($user);
            } catch (OverfastProfileNotFoundException) {
                $syncFailed = true;
                Inertia::flash('toast', [
                    'type' => 'error',
                    'message' => 'Your Overwatch profile is private or unavailable on OverFast.',
                ]);
            } catch (ConnectionException|RequestException|UnexpectedValueException $exception) {
                $syncFailed = true;
                report($exception);

                Inertia::flash('toast', [
                    'type' => 'error',
                    'message' => 'Your Overwatch stats could not be imported right now. Please try again later.',
                ]);
            }

            $profile = $loadProfile();
        }

        return Inertia::render('Statistics/Index', [
            'playerId' => $playerId,
            'syncing' => $needsSync && ! $syncFailed && $profile?->allHeroesStats === null,
            'profile' => $profile?->only([
                'player_id',
                'name',
                'avatar',
                'title',
                'last_updated_at',
            ]),
            'ranks' => $profile?->ranks->map(fn (Rank $rank): array => [
                'role' => $rank->role->role_name,
                'season' => $rank->season,
                'tier' => $rank->tier,
                'division' => $rank->division,
            ])->values()->all() ?? [],
            'rolesStats' => $profile?->rolesStats->map(fn (RolesStats $stats): array => [
                'role' => $stats->role->role_name,
                'metrics' => $this->metrics($stats, ['roles_stats_id', 'role_id', 'player_id']),
            ])->values()->all() ?? [],
            'allHeroesStats' => $profile?->allHeroesStats
                ? $this->metrics($profile->allHeroesStats, ['player_id'])
                : null,
            'heroStats' => $profile?->heroStats->map(fn (HeroStats $stats): array => [
                'hero' => $stats->hero->hero_name,
                'metrics' => $this->metrics($stats, ['id', 'player_id', 'hero_id']),
            ])->values()->all() ?? [],
            'heroSpecificStats' => $profile?->heroSpecificStats->map(fn (HeroSpecificStat $stat): array => [
                'hero' => $stat->hero->hero_name,
                'key' => $stat->stat_key,
                'value' => $stat->stat_value,
            ])->values()->all() ?? [],
        ]);
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
