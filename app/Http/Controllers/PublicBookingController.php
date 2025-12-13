<?php

namespace App\Http\Controllers;

use App\Enums\AvailabilityFrameStatus;
use App\Enums\AvailabilitySlotStatus;
use App\Enums\AvailabilityType;
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
}
