<?php

use App\Http\Controllers\BattlenetController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StatisticsController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'show'])->name('dashboard');
    Route::get('{playerId}/stats', [StatisticsController::class, 'show'])->name('statistics.show');
    Route::post('{playerId}/stats/sync', [StatisticsController::class, 'sync'])->name('statistics.sync');
    Route::get('chat', [ChatController::class, 'page'])->name('chat');
    Route::get('chat/conversations/{conversation}', [ChatController::class, 'conversation'])
        ->name('chat.conversations.show');
    Route::post('chat/message', [ChatController::class, 'message'])->name('chat.message');
    Route::inertia('pricing', 'Pricing')->name('pricing');
    Route::inertia('about-us', 'About')->name('about');
});

Route::get('/auth/battlenet', [BattlenetController::class, 'redirect'])->name('battlenet.redirect');
Route::get('/auth/battlenet/callback', [BattlenetController::class, 'callback'])->name('battlenet.callback');

require __DIR__.'/settings.php';
