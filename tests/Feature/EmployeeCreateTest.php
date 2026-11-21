<?php

use App\Livewire\Employees\Create;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function addEmployee(User $admin, array $values)
{
    $component = Livewire::actingAs($admin)->test(Create::class);

    foreach ($values as $field => $value) {
        $component->set($field, $value);
    }

    return $component->call('save');
}

test('guests are sent to login', function () {
    $this->get('/employees/create')->assertRedirect('/login');
});

test('front desk cannot open the add form or submit it', function () {
    $user = User::factory()->frontDesk()->create();

    $this->actingAs($user)->get('/employees/create')->assertForbidden();
    Livewire::actingAs($user)->test(Create::class)->assertForbidden();
});

test('admin can open the add employee form', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/employees/create')
        ->assertOk()
        ->assertSee('Save employee');
});

test('admin can add an employee with all fields', function () {
    $admin = User::factory()->admin()->create();

    addEmployee($admin, [
        'full_name' => 'Maria Santos',
        'job_title' => 'Therapist',
        'birthdate' => '1995-04-12',
        'gender' => 'Female',
        'contact_number' => '09171234567',
        'email' => 'maria@example.com',
        'address' => '123 Sample Street',
    ])->assertHasNoErrors()->assertRedirect(route('employees.index'));

    $this->assertDatabaseHas('employees', [
        'employee_id' => 'EMP-001',
        'full_name' => 'Maria Santos',
        'job_title' => 'Therapist',
        'gender' => 'Female',
        'contact_number' => '09171234567',
        'email' => 'maria@example.com',
        'address' => '123 Sample Street',
    ]);

    expect(Employee::first()->birthdate->format('Y-m-d'))->toBe('1995-04-12')
        ->and(session('status'))->toContain('Maria Santos')->toContain('EMP-001');
});

test('only full name and job title are required', function () {
    $admin = User::factory()->admin()->create();

    addEmployee($admin, ['full_name' => 'Ana Reyes', 'job_title' => 'Front Desk Staff'])
        ->assertHasNoErrors();

    $employee = Employee::first();

    expect($employee->birthdate)->toBeNull()
        ->and($employee->gender)->toBeNull()
        ->and($employee->contact_number)->toBeNull()
        ->and($employee->email)->toBeNull()
        ->and($employee->address)->toBeNull();
});

test('full name and job title are required', function () {
    $admin = User::factory()->admin()->create();

    addEmployee($admin, ['full_name' => '   ', 'job_title' => ''])
        ->assertHasErrors(['full_name' => 'required', 'job_title' => 'required']);

    expect(Employee::count())->toBe(0);
});

test('email format is validated', function () {
    $admin = User::factory()->admin()->create();

    addEmployee($admin, ['full_name' => 'A B', 'job_title' => 'Therapist', 'email' => 'not-an-email'])
        ->assertHasErrors(['email' => 'email']);
});

test('birthdate must be a real date in the past', function () {
    $admin = User::factory()->admin()->create();

    foreach (['2026-02-31', 'not a date', now()->addDay()->format('Y-m-d'), '1850-01-01'] as $bad) {
        addEmployee($admin, ['full_name' => 'A B', 'job_title' => 'Therapist', 'birthdate' => $bad])
            ->assertHasErrors(['birthdate']);
    }

    expect(Employee::count())->toBe(0);
});

test('contact number format is validated', function () {
    $admin = User::factory()->admin()->create();

    addEmployee($admin, ['full_name' => 'A B', 'job_title' => 'Therapist', 'contact_number' => 'call me'])
        ->assertHasErrors(['contact_number' => 'regex']);
});

test('gender must be one of the listed options', function () {
    $admin = User::factory()->admin()->create();

    addEmployee($admin, ['full_name' => 'A B', 'job_title' => 'Therapist', 'gender' => 'Robot'])
        ->assertHasErrors(['gender']);
});

test('input is trimmed and the email is lowercased', function () {
    $admin = User::factory()->admin()->create();

    addEmployee($admin, [
        'full_name' => '  Maria Santos  ',
        'job_title' => ' Therapist ',
        'email' => '  Maria@Example.COM ',
    ])->assertHasNoErrors();

    $this->assertDatabaseHas('employees', [
        'full_name' => 'Maria Santos',
        'job_title' => 'Therapist',
        'email' => 'maria@example.com',
    ]);
});

test('employee ids are generated one after another', function () {
    $admin = User::factory()->admin()->create();

    addEmployee($admin, ['full_name' => 'One', 'job_title' => 'Therapist']);
    addEmployee($admin, ['full_name' => 'Two', 'job_title' => 'Therapist']);

    expect(Employee::orderBy('id')->pluck('employee_id')->all())->toBe(['EMP-001', 'EMP-002']);
});

test('the list shows the success message after saving', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->withSession(['status' => 'Maria Santos was added as EMP-001.'])
        ->get('/employees')
        ->assertSee('Maria Santos was added as EMP-001.');
});