<?php

use Illuminate\Support\Facades\Route;
use App\Models\Employee;

Route::view('/', 'welcome')->name('home');

require __DIR__.'/settings.php';

Route::view('dashboard', 'dashboard')
    ->middleware('auth')
    ->name('dashboard');

Route::middleware(['auth', 'can:admin'])->group(function () {
    Route::view('users', 'users.index')->name('users.index');

    Route::view('employees', 'employees.index')->name('employees.index');

    Route::get('employees/{employee}', fn (Employee $employee) => view('employees.show', ['employee' => $employee]))
        ->name('employees.show');

    Route::get('employees/{employee}/edit', fn (Employee $employee) => view('employees.edit', ['employee' => $employee]))
        ->name('employees.edit');
});