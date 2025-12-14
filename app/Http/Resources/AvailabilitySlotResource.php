<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AvailabilitySlotResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'availability_frame_id' => $this->availability_frame_id,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'status' => $this->status,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];

        // Include appointment data if slot is booked and appointment is loaded
        if ($this->relationLoaded('appointment') && $this->appointment) {
            $data['appointment'] = [
                'id' => $this->appointment->id,
                'visitor_name' => $this->appointment->visitor_name,
                'student_name' => $this->appointment->student_name,
                'phone_number' => $this->appointment->phone_number,
                'email' => $this->appointment->email,
                'purpose' => $this->appointment->purpose,
                'status' => $this->appointment->status,
            ];
        }

        return $data;
    }
}
