<?php

use App\Livewire\Employees\Create;
use App\Livewire\Employees\Edit;
use App\Livewire\Employees\Index;
use App\Livewire\Employees\StatusToggle;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('new employees are active by default', function () {
    $employee = Employee::create(['full_name' => 'Ana Reyes', 'job_title' => 'Therapist']);

    expect($employee->is_active)->toBeTrue()
        ->and($employee->fresh()->is_active)->toBeTrue();
});

test('employees added through the form are active', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)->test(Create::class)
        ->set('full_name', 'Lara Mendoza')
        ->set('job_title', 'Therapist')
        ->call('save')
        ->assertHasNoErrors();

    expect(Employee::first()->is_active)->toBeTrue();
});

test('front desk cannot deactivate employees', function () {
    $user = User::factory()->frontDesk()->create();
    $employee = Employee::factory()->create();

    Livewire::actingAs($user)->test(Index::class)->assertForbidden();
    Livewire::actingAs($user)->test(StatusToggle::class, ['employee' => $employee])->assertForbidden();

    expect($employee->fresh()->is_active)->toBeTrue();
});

test('admin can deactivate and reactivate from the list', function () {
    $admin = User::factory()->admin()->create();
    $employee = Employee::factory()->create(['full_name' => 'Maria Santos']);

    $page = Livewire::actingAs($admin)->test(Index::class);

    $page->call('toggleActive', $employee->id)->assertSee('Maria Santos was deactivated.');
    expect($employee->fresh()->is_active)->toBeFalse();

    $page->call('toggleActive', $employee->id)->assertSee('Maria Santos was reactivated.');
    expect($employee->fresh()->is_active)->toBeTrue();
});

test('deactivating keeps the record and the employee id', function () {
    $admin = User::factory()->admin()->create();
    $employee = Employee::factory()->create(['full_name' => 'Maria Santos']);

    Livewire::actingAs($admin)->test(Index::class)->call('toggleActive', $employee->id);

    $fresh = $employee->fresh();

    expect(Employee::count())->toBe(1)
        ->and($fresh->employee_id)->toBe('EMP-001')
        ->and($fresh->full_name)->toBe('Maria Santos');
});

test('the list shows each employee status', function () {
    $admin = User::factory()->admin()->create();
    Employee::factory()->create(['full_name' => 'Active Person']);
    Employee::factory()->inactive()->create(['full_name' => 'Gone Person']);

    $this->actingAs($admin)->get('/employees')
        ->assertSee('Active Person')
        ->assertSee('Gone Person')
        ->assertSee('Inactive')
        ->assertSee('Deactivate')
        ->assertSee('Reactivate');
});

test('the status filter shows only the chosen status', function () {
    $admin = User::factory()->admin()->create();
    Employee::factory()->create(['full_name' => 'Active Person']);
    Employee::factory()->inactive()->create(['full_name' => 'Gone Person']);

    Livewire::actingAs($admin)->test(Index::class)
        ->set('statusFilter', 'active')
        ->assertSee('Active Person')
        ->assertDontSee('Gone Person');

    Livewire::actingAs($admin)->test(Index::class)
        ->set('statusFilter', 'inactive')
        ->assertSee('Gone Person')
        ->assertDontSee('Active Person');
});

test('reset also clears the status filter', function () {
    $admin = User::factory()->admin()->create();
    Employee::factory()->create(['full_name' => 'Active Person']);
    Employee::factory()->inactive()->create(['full_name' => 'Gone Person']);

    Livewire::actingAs($admin)->test(Index::class)
        ->set('statusFilter', 'inactive')
        ->call('resetFilters')
        ->assertSet('statusFilter', '')
        ->assertSee('Active Person')
        ->assertSee('Gone Person');
});

test('the status filter is kept in the page address', function () {
    $admin = User::factory()->admin()->create();
    Employee::factory()->create(['full_name' => 'Active Person']);
    Employee::factory()->inactive()->create(['full_name' => 'Gone Person']);

    Livewire::withQueryParams(['status' => 'inactive'])
        ->actingAs($admin)
        ->test(Index::class)
        ->assertSet('statusFilter', 'inactive')
        ->assertSee('Gone Person')
        ->assertDontSee('Active Person');
});

test('the active scope excludes inactive employees', function () {
    Employee::factory()->count(2)->create();
    Employee::factory()->inactive()->create();

    expect(Employee::active()->count())->toBe(2)
        ->and(Employee::count())->toBe(3);
});

test('the details page offers deactivate for active employees', function () {
    $admin = User::factory()->admin()->create();
    Employee::factory()->create();

    $this->actingAs($admin)->get('/employees/EMP-001')
        ->assertOk()
        ->assertSee('Deactivate')
        ->assertDontSee('Reactivate');
});

test('the details page offers reactivate for inactive employees', function () {
    $admin = User::factory()->admin()->create();
    Employee::factory()->inactive()->create();

    $this->actingAs($admin)->get('/employees/EMP-001')
        ->assertOk()
        ->assertSee('Inactive')
        ->assertSee('Reactivate');
});

test('toggling from the details page changes the status and returns there', function () {
    $admin = User::factory()->admin()->create();
    $employee = Employee::factory()->create(['full_name' => 'Maria Santos']);

    Livewire::actingAs($admin)->test(StatusToggle::class, ['employee' => $employee])
        ->call('toggle')
        ->assertRedirect(route('employees.show', $employee));

    expect($employee->fresh()->is_active)->toBeFalse()
        ->and(session('status'))->toContain('deactivated');
});

test('inactive employees can still be viewed and edited', function () {
    $admin = User::factory()->admin()->create();
    Employee::factory()->inactive()->create(['full_name' => 'Gone Person']);

    $this->actingAs($admin)->get('/employees/EMP-001')->assertOk()->assertSee('Gone Person');
    $this->actingAs($admin)->get('/employees/EMP-001/edit')->assertOk()->assertSee('Gone Person');
});

test('editing an employee does not change their status', function () {
    $admin = User::factory()->admin()->create();
    $employee = Employee::factory()->inactive()->create();

    Livewire::actingAs($admin)->test(Edit::class, ['employee' => $employee])
        ->set('full_name', 'Renamed Person')
        ->call('save')
        ->assertHasNoErrors();

    $fresh = $employee->fresh();

    expect($fresh->full_name)->toBe('Renamed Person')
        ->and($fresh->is_active)->toBeFalse();
});