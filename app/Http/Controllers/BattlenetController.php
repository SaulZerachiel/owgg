<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;


class BattlenetController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('battlenet')->redirect();
    }

    public function callback()
    {
        $bnet = Socialite::driver('battlenet')->user();

        $user = User::updateOrCreate(
            ['battlenet_id' => $bnet->getId()],
            ['name' => $bnet->getNickname(), 'battletag' => $bnet->getNickname()]
        );

        Auth::login($user);

        return redirect('/dashboard');
    }
}
