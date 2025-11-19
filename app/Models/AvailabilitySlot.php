<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\AvailabilitySlotStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AvailabilitySlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'availability_frame_id',
        'start_time',
        'end_time',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'status' => AvailabilitySlotStatus::class,
    ];

    public function availabilityFrame(): BelongsTo
    {
        return $this->belongsTo(AvailabilityFrame::class);
    }

    public function appointment(): HasOne
    {
        return $this->hasOne(Appointment::class);
    }
}
