<?php

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to login', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('admin sees finance menu items', function () {
    $user = User::factory()->create(['role' => Role::Admin]);

    $this->actingAs($user)->get('/dashboard')
        ->assertOk()->assertSee('Salary')->assertSee('Reports');
});

test('front desk does not see admin menu items', function () {
    $user = User::factory()->create(['role' => Role::FrontDesk]);

    $this->actingAs($user)->get('/dashboard')
        ->assertOk()->assertSee('Transactions')
        ->assertDontSee('Salary')->assertDontSee('Employees');
});

test('user can log out', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/logout');

    $this->assertGuest();
});

test('pages for signed-in users are not cached by the browser', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/dashboard');

    expect($response->headers->get('Cache-Control'))->toContain('no-store');
});