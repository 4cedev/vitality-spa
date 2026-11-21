<?php

use App\Livewire\Employees\Index;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('guests are sent to login', function () {
    $this->get('/employees')->assertRedirect('/login');
});

test('front desk cannot open employee pages', function () {
    $user = User::factory()->frontDesk()->create();
    $employee = Employee::factory()->create();

    $this->actingAs($user)->get('/employees')->assertForbidden();
    $this->actingAs($user)->get(route('employees.show', $employee))->assertForbidden();
    $this->actingAs($user)->get(route('employees.edit', $employee))->assertForbidden();
});

test('front desk cannot use the employee list component directly', function () {
    $user = User::factory()->frontDesk()->create();

    Livewire::actingAs($user)->test(Index::class)->assertForbidden();
});

test('admin sees employees in the table', function () {
    $admin = User::factory()->admin()->create();
    Employee::factory()->create(['full_name' => 'Maria Santos', 'job_title' => 'Therapist', 'contact_number' => '09171234567']);
    Employee::factory()->create(['full_name' => 'Ana Reyes', 'job_title' => 'Nail Technician']);

    $this->actingAs($admin)->get('/employees')
        ->assertOk()
        ->assertSee('EMP-001')
        ->assertSee('Maria Santos')
        ->assertSee('Therapist')
        ->assertSee('09171234567')
        ->assertSee('EMP-002')
        ->assertSee('Ana Reyes')
        ->assertSee('Nail Technician');
});

test('the table links to view and edit pages', function () {
    $admin = User::factory()->admin()->create();
    $employee = Employee::factory()->create();

    $this->actingAs($admin)->get('/employees')
        ->assertSee(route('employees.show', $employee), false)
        ->assertSee(route('employees.edit', $employee), false);
});

test('an empty state shows when there are no employees', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/employees')
        ->assertOk()
        ->assertSee('No employees yet');
});

test('a missing contact number shows a dash', function () {
    $admin = User::factory()->admin()->create();
    Employee::factory()->create(['contact_number' => null]);

    $this->actingAs($admin)->get('/employees')->assertOk()->assertSee('—');
});

test('the list is paginated ten per page', function () {
    $admin = User::factory()->admin()->create();
    Employee::factory()->count(12)->create();

    Livewire::actingAs($admin)->test(Index::class)
        ->assertSee('EMP-010')
        ->assertDontSee('EMP-011');
});

test('admin can open the view and edit pages by employee id', function () {
    $admin = User::factory()->admin()->create();
    $employee = Employee::factory()->create(['full_name' => 'Maria Santos']);

    $this->actingAs($admin)->get('/employees/EMP-001')->assertOk()->assertSee('Maria Santos');
    $this->actingAs($admin)->get('/employees/EMP-001/edit')->assertOk()->assertSee('Maria Santos');
});

test('an unknown employee id gives a 404', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/employees/EMP-999')->assertNotFound();
});