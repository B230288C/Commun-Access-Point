<?php

namespace App\Factories;

use App\Models\Appointment;
use App\Models\Staff;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppointmentFactory
{
    /**
     * Create a new appointment with business logic validation
     */
    public static function create(array $validated): Appointment
    {
        try {
            return DB::transaction(function () use ($validated) {
                $appointment = Appointment::create($validated);
                return $appointment->load('staff');
            });
        } catch (\Exception $e) {
            Log::error('Failed to create appointment', [
                'data' => $validated,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Update an appointment with business logic validation
     */
    public static function update(Appointment $appointment, array $validated): Appointment
    {
        try {
            return DB::transaction(function () use ($appointment, $validated) {
                $appointment->update($validated);
                return $appointment->fresh()->load('staff');
            });
        } catch (\Exception $e) {
            Log::error('Failed to update appointment', [
                'id' => $appointment->id,
                'data' => $validated,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Delete an appointment
     */
    public static function delete(Appointment $appointment): bool
    {
        try {
            return $appointment->delete();
        } catch (\Exception $e) {
            Log::error('Failed to delete appointment', [
                'id' => $appointment->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
