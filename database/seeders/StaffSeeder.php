<?php

namespace Database\Seeders;

use App\Models\Staff;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Staff::create([
            'name' => 'Test Staff',
            'email' => 'staff@example.com',
            'phone' => '1234567890',
            'department' => 'IT',
            'position' => 'Developer',
            'password' => Hash::make('password123'),
        ]);
    }
}
