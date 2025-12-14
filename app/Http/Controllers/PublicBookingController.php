<?php

namespace App\Http\Controllers;

use App\Enums\AvailabilityFrameStatus;
use App\Enums\AvailabilitySlotStatus;
use App\Enums\AvailabilityType;
use App\Factories\AppointmentFactory;
use App\Http\Requests\PublicBookingRequest;
use App\Models\AvailabilitySlot;
use App\Models\Staff;
use Illuminate\Http\JsonResponse;

class PublicBookingController extends Controller
{
    /**
     * Get staff details and available public slots for booking.
     *
     * Only returns slots from frames that are:
     * - status = 'active'
     * - availability_type = 'public'
     * - slot status = 'available'
     * - date >= today
     */
    public function getStaffAvailability(int $staffId): JsonResponse
    {
        $staff = Staff::find($staffId);

        if (!$staff) {
            return response()->json([
                'message' => 'Staff not found',
            ], 404);
        }

        $today = now()->format('Y-m-d');

        // Get frames that are active, public, and have future dates
        $frames = $staff->availabilityFrames()
            ->where('status', AvailabilityFrameStatus::Active->value)
            ->where('availability_type', AvailabilityType::Public->value)
            ->where('date', '>=', $today)
            ->with(['availabilitySlots' => function ($query) {
                $query->where('status', AvailabilitySlotStatus::Available->value)
                    ->orderBy('start_time');
            }])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        // Group slots by date
        $slotsByDate = [];

        foreach ($frames as $frame) {
            $date = $frame->date;

            if (!isset($slotsByDate[$date])) {
                $slotsByDate[$date] = [];
            }

            foreach ($frame->availabilitySlots as $slot) {
                $slotsByDate[$date][] = [
                    'id' => $slot->id,
                    'start_time' => $slot->start_time,
                    'end_time' => $slot->end_time,
                    'frame_id' => $frame->id,
                    'frame_title' => $frame->title,
                ];
            }
        }

        // Sort slots within each date by start_time
        foreach ($slotsByDate as $date => &$slots) {
            usort($slots, function ($a, $b) {
                return strcmp($a['start_time'], $b['start_time']);
            });
        }

        return response()->json([
            'staff' => [
                'id' => $staff->id,
                'name' => $staff->name,
                'department' => $staff->department,
                'position' => $staff->position,
            ],
            'slots_by_date' => $slotsByDate,
        ]);
    }

    /**
     * Show the booking page view.
     */
    public function show(int $staffId)
    {
        $staff = Staff::find($staffId);

        if (!$staff) {
            abort(404, 'Staff not found');
        }

        return view('booking', ['staffId' => $staffId]);
    }

    /**
     * Create a new appointment from the public booking page.
     */
    public function store(PublicBookingRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Verify the slot is still available
        $slot = AvailabilitySlot::with('availabilityFrame')->find($validated['availability_slot_id']);

        if (!$slot) {
            return response()->json([
                'message' => 'The selected time slot no longer exists.',
            ], 404);
        }

        if ($slot->status !== AvailabilitySlotStatus::Available) {
            return response()->json([
                'message' => 'This time slot is no longer available. Please select another slot.',
            ], 422);
        }

        // Verify the slot belongs to the staff
        if ($slot->availabilityFrame->staff_id !== (int) $validated['staff_id']) {
            return response()->json([
                'message' => 'Invalid slot selection.',
            ], 422);
        }

        // Create the appointment
        $appointment = AppointmentFactory::create($validated);

        // Update the slot status to booked
        $slot->update(['status' => AvailabilitySlotStatus::Booked]);

        return response()->json([
            'message' => 'Appointment booked successfully.',
            'data' => [
                'id' => $appointment->id,
                'visitor_name' => $appointment->visitor_name,
                'student_name' => $appointment->student_name,
                'email' => $appointment->email,
                'date' => $slot->availabilityFrame->date,
                'start_time' => $slot->start_time,
                'end_time' => $slot->end_time,
            ],
        ], 201);
    }
}
