<?php

use App\Http\Controllers\BattlenetController;
use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
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
