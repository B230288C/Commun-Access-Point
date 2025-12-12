<?php

namespace App\Repositories;

use App\Models\AvailabilitySlot;

class AvailabilitySlotRepository
{
    public function getAll()
    {
        return AvailabilitySlot::with(['availabilityFrame:id,date'])->get();
    }

    public function create(array $data): AvailabilitySlot
    {
        return AvailabilitySlot::create($data);
    }

    public function find(int $id): AvailabilitySlot
    {
        return AvailabilitySlot::find($id);
    }

    public function findOrFail(int $id): AvailabilitySlot
    {
        return AvailabilitySlot::findOrFail($id);
    }

    public function getByFrame(int $frameId)
    {
        return AvailabilitySlot::where('availability_frame_id', $frameId)->get();
    }

    public function update(int $id, array $data): AvailabilitySlot
    {
        $slot = AvailabilitySlot::findOrFail($id);

        $slot->update($data);

        return $slot;
    }

    public function delete(int $id): bool
    {
        $slot = AvailabilitySlot::find($id);

        if (!$slot) {
            return false;
        }

        if ($slot->appointment()->exists()) {
            return false;
        }

        return $slot->delete();
    }

    public function setStatus(int $slotId, string $status): AvailabilitySlot
    {
        $slot = AvailabilitySlot::findOrFail($slotId);
        $slot->update(['status' => $status]);
        return $slot;
    }

    /**
     * Check if a slot overlaps with existing slots within the same frame.
     * Uses strict inequalities to allow abutting events: (ExistingStart < NewEnd) AND (ExistingEnd > NewStart)
     *
     * @param int $frameId
     * @param string $startTime
     * @param string $endTime
     * @param int|null $excludeSlotId Slot ID to exclude (for updates)
     * @return bool True if overlap exists
     */
    public function hasOverlap(int $frameId, string $startTime, string $endTime, ?int $excludeSlotId = null): bool
    {
        $query = AvailabilitySlot::where('availability_frame_id', $frameId)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime);

        if ($excludeSlotId !== null) {
            $query->where('id', '!=', $excludeSlotId);
        }

        return $query->exists();
    }

    /**
     * Get overlapping slots for error reporting.
     *
     * @param int $frameId
     * @param string $startTime
     * @param string $endTime
     * @param int|null $excludeSlotId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getOverlappingSlots(int $frameId, string $startTime, string $endTime, ?int $excludeSlotId = null)
    {
        $query = AvailabilitySlot::where('availability_frame_id', $frameId)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime);

        if ($excludeSlotId !== null) {
            $query->where('id', '!=', $excludeSlotId);
        }

        return $query->get();
    }
}
