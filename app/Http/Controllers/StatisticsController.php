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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
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
            ->where('user_id', $user->id)
            ->orderByDesc('updated_at')
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
        $syncStatusKey = ImportPlayerStats::syncStatusCacheKey($user->getKey());
        $backgroundSyncFailed = Cache::get($syncStatusKey) === 'failed';

        if ($backgroundSyncFailed) {
            Cache::forget($syncStatusKey);
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Your stats import failed. Please try again later.',
            ]);
        } elseif ($needsSync) {
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
            $user->refresh();
        }

        $syncing = Cache::get($syncStatusKey) === 'requested'
            || ($needsSync && ! $syncFailed && ! $backgroundSyncFailed && (
                $profile?->allHeroesStats === null
                || $user->stats_synced_at === null
                || $user->stats_synced_at->lt(now()->subHour())
            ));

        return Inertia::render('Statistics/Index', [
            'playerId' => $playerId,
            'syncing' => $syncing,
            'statsSyncedAt' => $user->stats_synced_at?->toIso8601String(),
            'syncUrl' => route('statistics.sync', ['playerId' => $playerId]),
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

    public function sync(Request $request, string $playerId): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->battletag === $playerId, 404);

        $rateLimitKey = 'statistics-sync:'.$user->getKey();
        $syncStatusKey = ImportPlayerStats::syncStatusCacheKey($user->getKey());

        if (RateLimiter::tooManyAttempts($rateLimitKey, 1)) {
            $waitSeconds = RateLimiter::availableIn($rateLimitKey);
            Inertia::flash('toast', [
                'type' => 'warning',
                'message' => "Please wait {$waitSeconds} seconds before requesting another stats import.",
            ]);

            return to_route('statistics.show', ['playerId' => $playerId]);
        }

        RateLimiter::hit($rateLimitKey, 300);
        Cache::put($syncStatusKey, 'requested', now()->addMinutes(10));

        try {
            ImportPlayerStats::dispatch($user);
        } catch (OverfastProfileNotFoundException) {
            RateLimiter::clear($rateLimitKey);
            Cache::forget($syncStatusKey);
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Your Overwatch profile is private or unavailable on OverFast.',
            ]);

            return to_route('statistics.show', ['playerId' => $playerId]);
        } catch (ConnectionException|RequestException|UnexpectedValueException $exception) {
            RateLimiter::clear($rateLimitKey);
            Cache::forget($syncStatusKey);
            report($exception);
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Your Overwatch stats could not be imported right now. Please try again later.',
            ]);

            return to_route('statistics.show', ['playerId' => $playerId]);
        }

        Inertia::flash('toast', [
            'type' => 'info',
            'message' => 'A stats refresh was requested. The page will update after the import finishes.',
        ]);

        return to_route('statistics.show', ['playerId' => $playerId]);
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
