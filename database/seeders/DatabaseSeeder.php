<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            $this->command?->warn('Dev accounts are only seeded in local environments.');

            return;
        }
        
        $accounts = [
            ['Owner', 'owner@vitalityspa.test', \App\Enums\Role::Admin],
            ['Front Desk', 'frontdesk@vitalityspa.test', \App\Enums\Role::FrontDesk],
        ];

        foreach ($accounts as [$name, $email, $role]) {
            if (! User::where('email', $email)->exists()) {
                User::forceCreate([
                    'name' => $name,
                    'email' => $email,
                    'password' => 'ChangeMe123!',
                    'role' => $role,
                    'email_verified_at' => now(),
                ]);
            }
        }
    }
}
