<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

require __DIR__.'/settings.php';

Route::view('dashboard', 'dashboard')
    ->middleware('auth')
    ->name('dashboard');

Route::middleware(['auth', 'can:admin'])->group(function () {
    Route::view('users', 'users.index')->name('users.index');
});