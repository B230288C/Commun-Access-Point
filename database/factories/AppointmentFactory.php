<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Appointment;
use App\Models\Staff;
use App\Enums\AppointmentStatus;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        return [
            'visitor_name' => $this->faker->name(),
            'nric_passport' => strtoupper($this->faker->bothify('A########')),
            'phone_number' => $this->faker->phoneNumber(),
            'email' => $this->faker->safeEmail(),
            'purpose' => $this->faker->sentence(3),
            'personal_in_charge' => $this->faker->name(),
            'date' => $this->faker->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'start_time' => $this->faker->time('H:i'),
            'end_time' => $this->faker->time('H:i'),
            'status' => $this->faker->randomElement([
                AppointmentStatus::Pending->value,
                AppointmentStatus::Approved->value,
                AppointmentStatus::Cancelled->value,
                AppointmentStatus::Completed->value,
            ]),
            'staff_id' => Staff::factory(), // 自动关联 staff
        ];
    }
}
