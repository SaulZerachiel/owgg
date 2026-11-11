<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BattlenetController;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

Route::get('/auth/battlenet', [BattlenetController::class, 'redirect'])->name('battlenet.redirect');
Route::get('/auth/battlenet/callback', [BattlenetController::class, 'callback']);

require __DIR__.'/settings.php';
