<?php

namespace App\Http\Controllers;

use App\Jobs\ImportPlayerStats;
use App\Models\User;
use App\Services\OverfastProfileNotFoundException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;
use UnexpectedValueException;

class BattlenetController extends Controller
{
    public function redirect(): RedirectResponse
    {
        // Socialite construit l'URL OAuth avec les identifiants stockés côté serveur.
        return Socialite::driver('battlenet')->redirect();
    }

    public function callback(): RedirectResponse
    {
        // Le provider Battle.net expose l'identifiant stable et le BattleTag du joueur.
        $bnet = Socialite::driver('battlenet')->user();
        $battlenetId = (string) $bnet->getId();
        $battletag = str_replace('#', '-', $bnet->getNickname() ?? 'Joueur Overwatch');

        $user = User::updateOrCreate(
            ['battlenet_id' => $battlenetId],
            [
                // Battle.net ne fournit pas forcément d'e-mail : on utilise une adresse interne unique.
                'name' => $battletag,
                'email' => $bnet->getEmail() ?? $battlenetId.'@battlenet.local',
                'email_verified_at' => now(),
                'battletag' => $battletag,
            ],
        );

        Auth::login($user);

        if ($user->stats_synced_at === null || $user->stats_synced_at->lt(now()->subHour())) {
            try {
                ImportPlayerStats::dispatch($user);
            } catch (OverfastProfileNotFoundException) {
                Inertia::flash('toast', [
                    'type' => 'error',
                    'message' => 'Your Overwatch profile is private or unavailable on OverFast.',
                ]);
            } catch (ConnectionException|RequestException|UnexpectedValueException $exception) {
                report($exception);

                Inertia::flash('toast', [
                    'type' => 'error',
                    'message' => 'Your Overwatch stats could not be imported right now. Please try again later.',
                ]);
            }
        }

        return redirect()->intended(route('dashboard'));
    }
}
