<?php

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

beforeEach(function () {
    Route::middleware(['web', 'auth', 'can:admin'])
        ->get('/_test/admin-only', fn () => 'ok');
});

test('front desk cannot open admin pages', function () {
    $user = User::factory()->create(['role' => Role::FrontDesk]);

    $this->actingAs($user)->get('/_test/admin-only')->assertForbidden();
});

test('admin can open admin pages', function () {
    $user = User::factory()->create(['role' => Role::Admin]);

    $this->actingAs($user)->get('/_test/admin-only')->assertOk();
});

test('guests are sent to login', function () {
    $this->get('/_test/admin-only')->assertRedirect('/login');
});