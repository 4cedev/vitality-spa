<?php

use App\Livewire\Employees\Create;
use App\Livewire\Employees\Edit;
use App\Livewire\Employees\Index;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function seedStaff(): void
{
    Employee::factory()->create(['full_name' => 'Maria Santos', 'job_title' => 'Therapist']);        // EMP-001
    Employee::factory()->create(['full_name' => 'Ana Reyes', 'job_title' => 'Nail Technician']);     // EMP-002
    Employee::factory()->create(['full_name' => 'Mario Dela Cruz', 'job_title' => 'Therapist']);     // EMP-003
}

function listAs(User $user)
{
    return Livewire::actingAs($user)->test(Index::class);
}

test('front desk cannot use the searchable list', function () {
    $user = User::factory()->frontDesk()->create();

    listAs($user)->assertForbidden();
});

test('search finds employees by part of the name', function () {
    $admin = User::factory()->admin()->create();
    seedStaff();

    listAs($admin)->set('search', 'mar')
        ->assertSee('Maria Santos')
        ->assertSee('Mario Dela Cruz')
        ->assertDontSee('Ana Reyes');
});

test('search ignores capital letters and extra spaces', function () {
    $admin = User::factory()->admin()->create();
    seedStaff();

    listAs($admin)->set('search', '  MARIA   santos ')
        ->assertSee('Maria Santos')
        ->assertDontSee('Ana Reyes');
});

test('search finds employees by employee id', function () {
    $admin = User::factory()->admin()->create();
    seedStaff();

    listAs($admin)->set('search', 'EMP-002')->assertSee('Ana Reyes')->assertDontSee('Maria Santos');
    listAs($admin)->set('search', 'emp-002')->assertSee('Ana Reyes')->assertDontSee('Maria Santos');
    listAs($admin)->set('search', '002')->assertSee('Ana Reyes')->assertDontSee('Maria Santos');
});

test('search finds an employee that would be on another page', function () {
    $admin = User::factory()->admin()->create();
    Employee::factory()->count(12)->create();

    listAs($admin)->set('search', 'EMP-012')
        ->assertSee('EMP-012')
        ->assertDontSee('EMP-011');
});

test('the job title filter shows only matching employees', function () {
    $admin = User::factory()->admin()->create();
    seedStaff();

    listAs($admin)->set('jobTitle', 'Nail Technician')
        ->assertSee('Ana Reyes')
        ->assertDontSee('Maria Santos')
        ->assertDontSee('Mario Dela Cruz');
});

test('search and filter work together', function () {
    $admin = User::factory()->admin()->create();
    seedStaff();

    listAs($admin)->set('search', 'mari')->set('jobTitle', 'Therapist')
        ->assertSee('Maria Santos')
        ->assertSee('Mario Dela Cruz')
        ->assertDontSee('Ana Reyes');

    listAs($admin)->set('search', 'ana')->set('jobTitle', 'Therapist')
        ->assertDontSee('Ana Reyes')
        ->assertSee('No matching employees');
});

test('reset clears the search and the filter', function () {
    $admin = User::factory()->admin()->create();
    seedStaff();

    listAs($admin)->set('search', 'ana')->set('jobTitle', 'Nail Technician')
        ->call('resetFilters')
        ->assertSet('search', '')
        ->assertSet('jobTitle', '')
        ->assertSee('Maria Santos')
        ->assertSee('Ana Reyes')
        ->assertSee('Mario Dela Cruz');
});

test('no matches shows a helpful empty state, not the no employees state', function () {
    $admin = User::factory()->admin()->create();
    seedStaff();

    listAs($admin)->set('search', 'zzzz')
        ->assertSee('No matching employees')
        ->assertDontSee('No employees yet');
});

test('an empty database still shows the no employees state', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/employees')
        ->assertSee('No employees yet')
        ->assertDontSee('Search by name or Employee ID');
});

test('percent and underscore are searched literally', function () {
    $admin = User::factory()->admin()->create();
    seedStaff();

    listAs($admin)->set('search', '%')->assertSee('No matching employees');
    listAs($admin)->set('search', '_')->assertSee('No matching employees');
});

test('the header shows how many employees match', function () {
    $admin = User::factory()->admin()->create();
    seedStaff();

    listAs($admin)->set('jobTitle', 'Therapist')->assertSee('Showing 2 of 3');
    listAs($admin)->assertSee('3 employees registered');
});

test('the job title dropdown lists only titles that exist', function () {
    $admin = User::factory()->admin()->create();
    seedStaff();

    listAs($admin)
        ->assertSee('All job titles')
        ->assertSee('Nail Technician')
        ->assertSee('Therapist')
        ->assertDontSee('Front Desk Staff');
});

test('search and filter are kept in the page address', function () {
    $admin = User::factory()->admin()->create();
    seedStaff();

    Livewire::withQueryParams(['search' => 'Ana', 'job' => 'Nail Technician'])
        ->actingAs($admin)
        ->test(Index::class)
        ->assertSet('search', 'Ana')
        ->assertSet('jobTitle', 'Nail Technician')
        ->assertSee('Ana Reyes')
        ->assertDontSee('Maria Santos');
});

test('the employee module works from beginning to end', function () {
    $admin = User::factory()->admin()->create();

    // 1. Nothing registered yet.
    $this->actingAs($admin)->get('/employees')->assertOk()->assertSee('No employees yet');

    // 2. Add an employee.
    Livewire::actingAs($admin)->test(Create::class)
        ->set('full_name', 'Lara Mendoza')
        ->set('job_title', 'Therapist')
        ->set('contact_number', '09171234567')
        ->call('save')
        ->assertHasNoErrors();

    // 3. It appears in the list with a generated ID and can be searched.
    $this->actingAs($admin)->get('/employees')
        ->assertSee('EMP-001')->assertSee('Lara Mendoza')->assertSee('09171234567');

    listAs($admin)->set('search', 'lara')->assertSee('Lara Mendoza');
    listAs($admin)->set('search', 'EMP-001')->assertSee('Lara Mendoza');

    // 4. Its details page works.
    $this->actingAs($admin)->get('/employees/EMP-001')
        ->assertOk()->assertSee('Lara Mendoza')->assertSee('Not provided');

    // 5. Edit it.
    Livewire::actingAs($admin)->test(Edit::class, ['employee' => Employee::first()])
        ->set('full_name', 'Lara Mendoza-Cruz')
        ->set('job_title', 'Senior Therapist')
        ->call('save')
        ->assertHasNoErrors();

    // 6. The change shows up everywhere, and the ID is unchanged.
    $this->actingAs($admin)->get('/employees/EMP-001')->assertSee('Lara Mendoza-Cruz');
    listAs($admin)->set('search', 'cruz')->assertSee('Lara Mendoza-Cruz');
    listAs($admin)->set('jobTitle', 'Senior Therapist')->assertSee('Lara Mendoza-Cruz');
    listAs($admin)->set('jobTitle', 'Therapist')->assertSee('No matching employees');

    expect(Employee::first()->employee_id)->toBe('EMP-001');

    // 7. Front Desk cannot reach any of it.
    $frontDesk = User::factory()->frontDesk()->create();

    foreach (['/employees', '/employees/create', '/employees/EMP-001', '/employees/EMP-001/edit'] as $url) {
        $this->actingAs($frontDesk)->get($url)->assertForbidden();
    }
});