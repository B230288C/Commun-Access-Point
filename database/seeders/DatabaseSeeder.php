<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Staff;
use App\Models\Appointment;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create staff members
        Staff::factory(5)->create();

        // Create sample appointments
        Appointment::factory(10)->create();
    }
}
