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
}
