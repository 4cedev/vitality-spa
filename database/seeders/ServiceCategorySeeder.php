<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Massage Services', 'Body Scrub Services', 'Nail Care Services'] as $name) {
            ServiceCategory::firstOrCreate(['category_name' => $name]);
        }
    }
}