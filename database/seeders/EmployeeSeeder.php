<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        if (Employee::exists()) {
            return;
        }

        Employee::factory()
            ->count(6)
            ->sequence(
                ['job_title' => 'Therapist'],
                ['job_title' => 'Therapist'],
                ['job_title' => 'Therapist'],
                ['job_title' => 'Nail Technician'],
                ['job_title' => 'Nail Technician'],
                ['job_title' => 'Front Desk Staff'],
            )
            ->create();
    }
}