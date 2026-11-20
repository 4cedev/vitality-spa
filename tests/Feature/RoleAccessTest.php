<?php

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('front desk cannot open admin pages', function () {
    $user = User::factory()->create(['role' => Role::FrontDesk]);

    $this->actingAs($user)->get('/admin-check')->assertForbidden();
});

test('admin can open admin pages', function () {
    $user = User::factory()->create(['role' => Role::Admin]);

    $this->actingAs($user)->get('/admin-check')->assertOk();
});