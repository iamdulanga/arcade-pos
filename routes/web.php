<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\PosTerminal;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';

// POS Terminal
Route::middleware(['auth'])->group(function () {
    Route::get('/pos', PosTerminal::class)->name('pos');
});