<?php

use App\Models\ServiceCategory;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

test('the service_categories table has the expected columns', function () {
    expect(Schema::hasColumns('service_categories', [
        'id', 'category_id', 'category_name', 'created_at', 'updated_at',
    ]))->toBeTrue();
});

test('the model is linked to the service_categories table', function () {
    expect((new ServiceCategory)->getTable())->toBe('service_categories');
});

test('a category can be stored and retrieved', function () {
    ServiceCategory::create(['category_name' => 'Massage Services']);

    $found = ServiceCategory::where('category_id', 'SC-001')->first();

    expect($found)->not->toBeNull()
        ->and($found->category_name)->toBe('Massage Services');
});

test('category ids are generated in order', function () {
    $first = ServiceCategory::factory()->create();
    $second = ServiceCategory::factory()->create();
    $third = ServiceCategory::factory()->create();

    expect($first->category_id)->toBe('SC-001')
        ->and($second->category_id)->toBe('SC-002')
        ->and($third->category_id)->toBe('SC-003');
});

test('numbering continues from the latest category', function () {
    ServiceCategory::factory()->create(['category_id' => 'SC-009']);

    expect(ServiceCategory::factory()->create()->category_id)->toBe('SC-010');
});

test('numbering grows past three digits', function () {
    ServiceCategory::factory()->create(['category_id' => 'SC-999']);

    expect(ServiceCategory::factory()->create()->category_id)->toBe('SC-1000');
});

test('category ids must be unique', function () {
    ServiceCategory::factory()->create();

    expect(fn () => ServiceCategory::factory()->create(['category_id' => 'SC-001']))
        ->toThrow(UniqueConstraintViolationException::class);
});

test('category names must be unique', function () {
    ServiceCategory::create(['category_name' => 'Massage Services']);

    expect(fn () => ServiceCategory::create(['category_name' => 'Massage Services']))
        ->toThrow(UniqueConstraintViolationException::class);
});

test('the category id cannot be set through mass assignment', function () {
    $category = ServiceCategory::create([
        'category_id' => 'SC-500',
        'category_name' => 'Massage Services',
    ]);

    expect($category->category_id)->toBe('SC-001');
});

test('the starter seeder creates the three categories once', function () {
    $this->seed(\Database\Seeders\ServiceCategorySeeder::class);
    $this->seed(\Database\Seeders\ServiceCategorySeeder::class);

    expect(ServiceCategory::orderBy('id')->pluck('category_name', 'category_id')->all())->toBe([
        'SC-001' => 'Massage Services',
        'SC-002' => 'Body Scrub Services',
        'SC-003' => 'Nail Care Services',
    ]);
});