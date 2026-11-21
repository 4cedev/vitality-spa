<?php

use App\Models\Employee;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

test('the employees table has the expected columns', function () {
    expect(Schema::hasColumns('employees', [
        'id', 'employee_id', 'full_name', 'job_title', 'birthdate', 'gender',
        'contact_number', 'email', 'address', 'created_at', 'updated_at',
    ]))->toBeTrue();
});

test('an employee can be stored and retrieved', function () {
    Employee::create([
        'full_name' => 'Maria Santos',
        'job_title' => 'Therapist',
        'birthdate' => '1995-04-12',
        'gender' => 'Female',
        'contact_number' => '09171234567',
        'email' => 'maria@example.com',
        'address' => '123 Sample Street',
    ]);

    $found = Employee::where('employee_id', 'EMP-001')->first();

    expect($found)->not->toBeNull()
        ->and($found->full_name)->toBe('Maria Santos')
        ->and($found->job_title)->toBe('Therapist')
        ->and($found->contact_number)->toBe('09171234567')
        ->and($found->birthdate->format('Y-m-d'))->toBe('1995-04-12');
});

test('employee ids are generated in order', function () {
    $first = Employee::factory()->create();
    $second = Employee::factory()->create();
    $third = Employee::factory()->create();

    expect($first->employee_id)->toBe('EMP-001')
        ->and($second->employee_id)->toBe('EMP-002')
        ->and($third->employee_id)->toBe('EMP-003');
});

test('numbering continues from the latest employee', function () {
    Employee::factory()->create(['employee_id' => 'EMP-009']);

    expect(Employee::factory()->create()->employee_id)->toBe('EMP-010');
});

test('numbering grows past three digits', function () {
    Employee::factory()->create(['employee_id' => 'EMP-999']);

    expect(Employee::factory()->create()->employee_id)->toBe('EMP-1000');
});

test('employee ids must be unique', function () {
    Employee::factory()->create();

    expect(fn () => Employee::factory()->create(['employee_id' => 'EMP-001']))
        ->toThrow(UniqueConstraintViolationException::class);
});

test('optional fields can be left empty', function () {
    $employee = Employee::create([
        'full_name' => 'Ana Reyes',
        'job_title' => 'Front Desk Staff',
    ]);

    expect($employee->fresh()->birthdate)->toBeNull()
        ->and($employee->fresh()->email)->toBeNull();
});