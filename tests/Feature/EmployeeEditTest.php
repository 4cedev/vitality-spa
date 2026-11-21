<?php

use App\Livewire\Employees\Edit;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function updateEmployee(User $admin, Employee $employee, array $values)
{
    $component = Livewire::actingAs($admin)->test(Edit::class, ['employee' => $employee]);

    foreach ($values as $field => $value) {
        $component->set($field, $value);
    }

    return $component->call('save');
}

function sampleEmployee(): Employee
{
    return Employee::factory()->create([
        'full_name' => 'Maria Santos',
        'job_title' => 'Therapist',
        'birthdate' => '1995-04-12',
        'gender' => 'Female',
        'contact_number' => '09171234567',
        'email' => 'maria@example.com',
        'address' => '123 Sample Street',
    ]);
}

test('guests are sent to login', function () {
    $employee = Employee::factory()->create();

    $this->get(route('employees.edit', $employee))->assertRedirect('/login');
});

test('front desk cannot open or submit the edit form', function () {
    $user = User::factory()->frontDesk()->create();
    $employee = Employee::factory()->create();

    $this->actingAs($user)->get(route('employees.edit', $employee))->assertForbidden();
    Livewire::actingAs($user)->test(Edit::class, ['employee' => $employee])->assertForbidden();
});

test('the form loads the existing information', function () {
    $admin = User::factory()->admin()->create();
    $employee = sampleEmployee();

    Livewire::actingAs($admin)->test(Edit::class, ['employee' => $employee])
        ->assertSet('full_name', 'Maria Santos')
        ->assertSet('job_title', 'Therapist')
        ->assertSet('birthdate', '1995-04-12')
        ->assertSet('gender', 'Female')
        ->assertSet('contact_number', '09171234567')
        ->assertSet('email', 'maria@example.com')
        ->assertSet('address', '123 Sample Street');
});

test('the edit page shows the employee and their id', function () {
    $admin = User::factory()->admin()->create();
    sampleEmployee();

    $this->actingAs($admin)->get('/employees/EMP-001/edit')
        ->assertOk()
        ->assertSee('EMP-001')
        ->assertSee('Maria Santos')
        ->assertSee('Save changes');
});

test('admin can update every field', function () {
    $admin = User::factory()->admin()->create();
    $employee = sampleEmployee();

    updateEmployee($admin, $employee, [
        'full_name' => 'Maria S. Cruz',
        'job_title' => 'Senior Therapist',
        'birthdate' => '1990-01-31',
        'gender' => 'Other',
        'contact_number' => '09998887777',
        'email' => 'maria.cruz@example.com',
        'address' => '456 New Street',
    ])->assertHasNoErrors()->assertRedirect(route('employees.show', $employee));

    $this->assertDatabaseHas('employees', [
        'id' => $employee->id,
        'full_name' => 'Maria S. Cruz',
        'job_title' => 'Senior Therapist',
        'gender' => 'Other',
        'contact_number' => '09998887777',
        'email' => 'maria.cruz@example.com',
        'address' => '456 New Street',
    ]);

    expect($employee->fresh()->birthdate->format('Y-m-d'))->toBe('1990-01-31')
        ->and(session('status'))->toContain('updated');
});

test('the employee id never changes when editing', function () {
    $admin = User::factory()->admin()->create();
    $employee = sampleEmployee();

    updateEmployee($admin, $employee, ['full_name' => 'Renamed Person'])->assertHasNoErrors();

    expect($employee->fresh()->employee_id)->toBe('EMP-001')
        ->and(property_exists(Edit::class, 'employee_id'))->toBeFalse();
});

test('editing one employee does not touch the others', function () {
    $admin = User::factory()->admin()->create();
    $first = sampleEmployee();
    $second = Employee::factory()->create(['full_name' => 'Other Person']);

    updateEmployee($admin, $first, ['full_name' => 'Changed Name']);

    expect($second->fresh()->full_name)->toBe('Other Person');
});

test('required fields are still required when editing', function () {
    $admin = User::factory()->admin()->create();
    $employee = sampleEmployee();

    updateEmployee($admin, $employee, ['full_name' => '  ', 'job_title' => ''])
        ->assertHasErrors(['full_name' => 'required', 'job_title' => 'required']);

    expect($employee->fresh()->full_name)->toBe('Maria Santos');
});

test('email, date and phone formats are validated when editing', function () {
    $admin = User::factory()->admin()->create();
    $employee = sampleEmployee();

    updateEmployee($admin, $employee, ['email' => 'not-an-email'])->assertHasErrors(['email' => 'email']);
    updateEmployee($admin, $employee, ['birthdate' => '2026-02-31'])->assertHasErrors(['birthdate']);
    updateEmployee($admin, $employee, ['birthdate' => now()->addDay()->format('Y-m-d')])->assertHasErrors(['birthdate']);
    updateEmployee($admin, $employee, ['contact_number' => 'call me'])->assertHasErrors(['contact_number' => 'regex']);
    updateEmployee($admin, $employee, ['gender' => 'Robot'])->assertHasErrors(['gender']);
});

test('optional fields can be cleared', function () {
    $admin = User::factory()->admin()->create();
    $employee = sampleEmployee();

    updateEmployee($admin, $employee, [
        'birthdate' => null,
        'gender' => '',
        'contact_number' => '',
        'email' => '',
        'address' => '',
    ])->assertHasNoErrors();

    $fresh = $employee->fresh();

    expect($fresh->birthdate)->toBeNull()
        ->and($fresh->gender)->toBeNull()
        ->and($fresh->contact_number)->toBeNull()
        ->and($fresh->email)->toBeNull()
        ->and($fresh->address)->toBeNull();
});

test('input is trimmed and the email is lowercased', function () {
    $admin = User::factory()->admin()->create();
    $employee = sampleEmployee();

    updateEmployee($admin, $employee, [
        'full_name' => '  Maria Cruz  ',
        'email' => '  Maria.Cruz@Example.COM ',
    ])->assertHasNoErrors();

    $this->assertDatabaseHas('employees', [
        'id' => $employee->id,
        'full_name' => 'Maria Cruz',
        'email' => 'maria.cruz@example.com',
    ]);
});

test('saving without changes says so', function () {
    $admin = User::factory()->admin()->create();
    $employee = sampleEmployee();

    updateEmployee($admin, $employee, [])->assertHasNoErrors();

    expect(session('status'))->toContain('No changes');
});

test('updates are reflected on the list and details pages', function () {
    $admin = User::factory()->admin()->create();
    $employee = sampleEmployee();

    updateEmployee($admin, $employee, ['full_name' => 'Maria Cruz', 'job_title' => 'Senior Therapist']);

    $this->actingAs($admin)->get('/employees')
        ->assertSee('Maria Cruz')
        ->assertSee('Senior Therapist')
        ->assertDontSee('Maria Santos');

    $this->actingAs($admin)->get('/employees/EMP-001')
        ->assertSee('Maria Cruz')
        ->assertDontSee('Maria Santos');
});

test('the details page shows the confirmation message', function () {
    $admin = User::factory()->admin()->create();
    sampleEmployee();

    $this->actingAs($admin)
        ->withSession(['status' => "Maria Santos's record was updated."])
        ->get('/employees/EMP-001')
        ->assertSee('record was updated');
});

test('an unknown employee id gives a 404', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/employees/EMP-999/edit')->assertNotFound();
});