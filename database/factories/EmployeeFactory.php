<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = fake()->randomElement(['Male', 'Female']);

        return [
            'full_name' => fake()->name(strtolower($gender)),
            'job_title' => fake()->randomElement(['Therapist', 'Nail Technician', 'Front Desk Staff']),
            'birthdate' => fake()->dateTimeBetween('-50 years', '-20 years')->format('Y-m-d'),
            'gender' => $gender,
            'contact_number' => '09'.fake()->numerify('#########'),
            'email' => fake()->unique()->safeEmail(),
            'address' => fake()->streetAddress(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
