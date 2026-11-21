<?php

use App\Livewire\Users\Index;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('a user who must change password is redirected to the password page', function () {
    $user = User::factory()->mustChangePassword()->create();

    $this->actingAs($user)->get('/dashboard')
        ->assertRedirect(route('security.edit'));
});

test('a flagged user can still open the password page', function () {
    $user = User::factory()->mustChangePassword()->create();

    $this->actingAs($user)->get(route('security.edit'))->assertOk();
});

test('a flagged user can still log out', function () {
    $user = User::factory()->mustChangePassword()->create();

    $this->actingAs($user)->post('/logout');

    $this->assertGuest();
});

test('a normal user is not redirected', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/dashboard')->assertOk();
});

test('changing your own password clears the flag', function () {
    $user = User::factory()->mustChangePassword()->create();

    $this->actingAs($user);
    $user->password = 'NewStr0ng-Pass-123!';
    $user->save();

    expect($user->fresh()->must_change_password)->toBeFalse();
});

test('new accounts created by an admin must change their password', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)->test(Index::class)
        ->call('create')
        ->set('name', 'New Staff')
        ->set('email', 'new.staff@example.com')
        ->set('role', 'front_desk')
        ->set('password', 'Str0ng-Pass-123!')
        ->call('save')
        ->assertHasNoErrors();

    expect(User::where('email', 'new.staff@example.com')->first()->must_change_password)->toBeTrue();
});

test('an admin resetting a password forces a change again', function () {
    $admin = User::factory()->admin()->create();
    $staff = User::factory()->frontDesk()->create();

    Livewire::actingAs($admin)->test(Index::class)
        ->call('edit', $staff->id)
        ->set('password', 'Reset-Pass-456!')
        ->call('save')
        ->assertHasNoErrors();

    expect($staff->fresh()->must_change_password)->toBeTrue();
});

test('resetting a password for someone already flagged keeps them flagged', function () {
    $admin = User::factory()->admin()->create();
    $staff = User::factory()->frontDesk()->mustChangePassword()->create();

    Livewire::actingAs($admin)->test(Index::class)
        ->call('edit', $staff->id)
        ->set('password', 'Reset-Pass-789!')
        ->call('save')
        ->assertHasNoErrors();

    expect($staff->fresh()->must_change_password)->toBeTrue();
});

test('an admin changing their own password is not forced to change it', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)->test(Index::class)
        ->call('edit', $admin->id)
        ->set('password', 'My-Own-Pass-123!')
        ->call('save')
        ->assertHasNoErrors();

    expect($admin->fresh()->must_change_password)->toBeFalse();
});