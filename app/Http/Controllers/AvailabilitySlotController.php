<?php

namespace App\Http\Controllers;

use App\Exceptions\SlotOverlapException;
use App\Http\Requests\StoreAvailabilitySlotRequest;
use App\Http\Requests\UpdateAvailabilitySlotRequest;
use App\Repositories\AvailabilitySlotRepository;
use Illuminate\Http\JsonResponse;

class AvailabilitySlotController extends Controller
{
    protected $slotRepo;

    public function __construct(AvailabilitySlotRepository $slotRepo)
    {
        $this->slotRepo = $slotRepo;
    }

    /**
     * Get all slots
     */
    public function index(): JsonResponse
    {
        $slots = $this->slotRepo->getAll();
        return response()->json($slots);
    }

    /**
     * Store a new slot
     */
    public function store(StoreAvailabilitySlotRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Check for overlaps before creating
        if ($this->slotRepo->hasOverlap($data['availability_frame_id'], $data['start_time'], $data['end_time'])) {
            $overlapping = $this->slotRepo->getOverlappingSlots($data['availability_frame_id'], $data['start_time'], $data['end_time']);
            $slotInfo = $overlapping->map(fn($s) => "{$s->start_time} - {$s->end_time}")->implode(', ');

            return response()->json([
                'message' => "Slot overlaps with existing slots: {$slotInfo}",
                'error' => 'overlap',
            ], 409);
        }

        $slot = $this->slotRepo->create($data);
        return response()->json($slot, 201);
    }

    /**
     * Show a single slot
     */
    public function show(int $id): JsonResponse
    {
        $slot = $this->slotRepo->findOrFail($id);
        return response()->json($slot);
    }

    /**
     * Update a slot
     */
    public function update(UpdateAvailabilitySlotRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();
        $existingSlot = $this->slotRepo->findOrFail($id);

        // Check for overlaps if time is being changed
        if (isset($data['start_time']) || isset($data['end_time'])) {
            $startTime = $data['start_time'] ?? $existingSlot->start_time;
            $endTime = $data['end_time'] ?? $existingSlot->end_time;

            if ($this->slotRepo->hasOverlap($existingSlot->availability_frame_id, $startTime, $endTime, $id)) {
                $overlapping = $this->slotRepo->getOverlappingSlots($existingSlot->availability_frame_id, $startTime, $endTime, $id);
                $slotInfo = $overlapping->map(fn($s) => "{$s->start_time} - {$s->end_time}")->implode(', ');

                return response()->json([
                    'message' => "Slot overlaps with existing slots: {$slotInfo}",
                    'error' => 'overlap',
                ], 409);
            }
        }

        $slot = $this->slotRepo->update($id, $data);
        return response()->json($slot);
    }

    /**
     * Delete a slot
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->slotRepo->delete($id);
        if (!$deleted) {
            return response()->json(['message' => 'Slot cannot be deleted, it may have an appointment'], 400);
        }
        return response()->json(['message' => 'Slot deleted successfully']);
    }
}
