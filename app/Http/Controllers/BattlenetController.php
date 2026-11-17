<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
        $battletag = $bnet->getNickname() ?? 'Joueur Overwatch';

        $user = User::updateOrCreate(
            ['battlenet_id' => $bnet->getId()],
            [
                // Battle.net ne fournit pas forcément d'e-mail : on utilise une adresse interne unique.
                'name' => $battletag,
                'email' => $bnet->getEmail() ?? $bnet->getId().'@battlenet.local',
                'email_verified_at' => now(),
                'battletag' => $battletag,
            ],
        );

        Auth::login($user);

        return redirect()->intended(route('dashboard'));
    }
}
