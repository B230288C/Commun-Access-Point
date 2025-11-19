<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\AppointmentStatus;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'visitor_name',
        'nric_passport',
        'phone_number',
        'email',
        'purpose',
        'status',
        'staff_id',
        'availability_slot_id',
    ];

    protected $casts = [
        'status' => AppointmentStatus::class, // 使用 Enum 转换
    ];

    /**
     * Appointment 属于一个 Staff
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function availabilitySlot(): BelongsTo
    {
        return $this->belongsTo(AvailabilitySlot::class);
    }
}
