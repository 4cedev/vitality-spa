<?php

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are sent to login', function () {
    $employee = Employee::factory()->create();

    $this->get(route('employees.show', $employee))->assertRedirect('/login');
});

test('front desk cannot view employee details', function () {
    $user = User::factory()->frontDesk()->create();
    $employee = Employee::factory()->create();

    $this->actingAs($user)->get(route('employees.show', $employee))->assertForbidden();
});

test('admin sees the complete employee record', function () {
    $admin = User::factory()->admin()->create();
    $employee = Employee::factory()->create([
        'full_name' => 'Maria Santos',
        'job_title' => 'Therapist',
        'birthdate' => '1995-04-12',
        'gender' => 'Female',
        'contact_number' => '09171234567',
        'email' => 'maria@example.com',
        'address' => '123 Sample Street',
    ]);

    $this->actingAs($admin)->get(route('employees.show', $employee))
        ->assertOk()
        ->assertSee('EMP-001')
        ->assertSee('Maria Santos')
        ->assertSee('Therapist')
        ->assertSee('April 12, 1995')
        ->assertSee($employee->birthdate->age.' years old')
        ->assertSee('Female')
        ->assertSee('09171234567')
        ->assertSee('mailto:maria@example.com', false)
        ->assertSee('maria@example.com')
        ->assertSee('123 Sample Street');
});

test('empty optional fields show not provided', function () {
    $admin = User::factory()->admin()->create();
    $employee = Employee::create(['full_name' => 'Ana Reyes', 'job_title' => 'Front Desk Staff']);

    $this->actingAs($admin)->get(route('employees.show', $employee))
        ->assertOk()
        ->assertSee('Ana Reyes')
        ->assertSee('Not provided');
});

test('the page links back to the list and to the edit page', function () {
    $admin = User::factory()->admin()->create();
    $employee = Employee::factory()->create();

    $this->actingAs($admin)->get(route('employees.show', $employee))
        ->assertSee('Back to Employee Records')
        ->assertSee(route('employees.index'), false)
        ->assertSee(route('employees.edit', $employee), false);
});

test('the view button on the list opens the right employee', function () {
    $admin = User::factory()->admin()->create();
    Employee::factory()->create(['full_name' => 'First Person']);
    $second = Employee::factory()->create(['full_name' => 'Second Person']);

    $this->actingAs($admin)->get('/employees')
        ->assertSee(route('employees.show', $second), false);

    $this->actingAs($admin)->get('/employees/EMP-002')
        ->assertOk()
        ->assertSee('Second Person')
        ->assertDontSee('First Person');
});

test('an unknown employee id gives a 404', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/employees/EMP-999')->assertNotFound();
});

test('html in employee data is escaped', function () {
    $admin = User::factory()->admin()->create();
    $employee = Employee::factory()->create([
        'full_name' => '<script>alert(1)</script>',
        'address' => '<b onmouseover=alert(2)>Street</b>',
    ]);

    $this->actingAs($admin)->get(route('employees.show', $employee))
        ->assertOk()
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertDontSee('<b onmouseover', false);
});