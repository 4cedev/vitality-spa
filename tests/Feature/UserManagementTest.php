<?php

use App\Enums\Role;
use App\Livewire\Users\Index;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('front desk cannot open the users page', function () {
    $user = User::factory()->frontDesk()->create();

    $this->actingAs($user)->get('/users')->assertForbidden();
});

test('front desk cannot use the users component directly', function () {
    $user = User::factory()->frontDesk()->create();

    Livewire::actingAs($user)->test(Index::class)->assertForbidden();
});

test('admin can open the users page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/users')->assertOk()->assertSee('Add user');
});

test('admin can create a front desk account', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)->test(Index::class)
        ->call('create')
        ->set('name', 'New Staff')
        ->set('email', 'Staff@Example.com')
        ->set('role', 'front_desk')
        ->set('password', 'Str0ng-Pass-123!')
        ->call('save')
        ->assertHasNoErrors();

    $created = User::where('email', 'staff@example.com')->first();

    expect($created)->not->toBeNull()
        ->and($created->role)->toBe(Role::FrontDesk)
        ->and($created->is_active)->toBeTrue();
});

test('email must be unique', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create(['email' => 'taken@example.com']);

    Livewire::actingAs($admin)->test(Index::class)
        ->call('create')
        ->set('name', 'Someone')
        ->set('email', 'taken@example.com')
        ->set('password', 'Str0ng-Pass-123!')
        ->call('save')
        ->assertHasErrors(['email']);
});

test('admin can change another user role', function () {
    $admin = User::factory()->admin()->create();
    $staff = User::factory()->frontDesk()->create();

    Livewire::actingAs($admin)->test(Index::class)
        ->call('edit', $staff->id)
        ->set('role', 'admin')
        ->call('save')
        ->assertHasNoErrors();

    expect($staff->fresh()->role)->toBe(Role::Admin);
});

test('admin cannot change their own role', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)->test(Index::class)
        ->call('edit', $admin->id)
        ->set('role', 'front_desk')
        ->call('save');

    expect($admin->fresh()->role)->toBe(Role::Admin);
});

test('admin cannot deactivate themselves', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)->test(Index::class)
        ->call('toggleActive', $admin->id);

    expect($admin->fresh()->is_active)->toBeTrue();
});

test('admin can deactivate and reactivate staff', function () {
    $admin = User::factory()->admin()->create();
    $staff = User::factory()->frontDesk()->create();

    $page = Livewire::actingAs($admin)->test(Index::class);

    $page->call('toggleActive', $staff->id);
    expect($staff->fresh()->is_active)->toBeFalse();

    $page->call('toggleActive', $staff->id);
    expect($staff->fresh()->is_active)->toBeTrue();
});

test('a deactivated user is signed out on their next request', function () {
    $user = User::factory()->inactive()->create();

    $this->actingAs($user)->get('/dashboard')->assertRedirect('/login');
    $this->assertGuest();
});

test('only admins see the users link', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get('/dashboard')->assertSee('Administration');

    $this->actingAs(User::factory()->frontDesk()->create())
        ->get('/dashboard')->assertDontSee('Administration');
});