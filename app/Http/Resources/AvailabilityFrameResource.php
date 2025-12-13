<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AvailabilityFrameResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'staff_id' => $this->staff_id,
            'date' => $this->date,
            'title' => $this->title,
            'day' => $this->day,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'duration' => $this->duration,
            'interval' => $this->interval,
            'is_recurring' => $this->is_recurring,
            'repeat_group_id' => $this->repeat_group_id,
            'status' => $this->status,
            'availability_type' => $this->availability_type?->value ?? 'public',
            'slots' => AvailabilitySlotResource::collection($this->whenLoaded('availabilitySlots')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
