<?php

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('create owner command makes an active administrator', function () {
    $this->artisan('spa:create-owner')
        ->expectsQuestion('Full name', 'Real Owner')
        ->expectsQuestion('Email address', 'Owner@VitalitySpa.ph')
        ->expectsQuestion('Password (min 12 characters)', 'Str0ng-Pass-123!')
        ->expectsQuestion('Confirm password', 'Str0ng-Pass-123!')
        ->assertSuccessful();

    $owner = User::where('email', 'owner@vitalityspa.ph')->first();

    expect($owner)->not->toBeNull()
        ->and($owner->role)->toBe(Role::Admin)
        ->and($owner->is_active)->toBeTrue();
});