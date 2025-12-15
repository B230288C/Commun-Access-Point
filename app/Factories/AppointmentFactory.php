<?php

namespace App\Factories;

use App\Enums\AvailabilitySlotStatus;
use App\Models\Appointment;
use App\Models\AvailabilitySlot;
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
     * Delete an appointment and set the slot status back to available
     */
    public static function delete(Appointment $appointment): bool
    {
        try {
            return DB::transaction(function () use ($appointment) {
                $slotId = $appointment->availability_slot_id;

                $appointment->delete();

                if ($slotId) {
                    AvailabilitySlot::where('id', $slotId)
                        ->update(['status' => AvailabilitySlotStatus::Available->value]);
                }

                return true;
            });
        } catch (\Exception $e) {
            Log::error('Failed to delete appointment', [
                'id' => $appointment->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
