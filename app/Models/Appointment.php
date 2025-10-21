<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
        'personal_in_charge',
        'date',
        'start_time',
        'end_time',
        'status',
        'staff_id',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'status' => AppointmentStatus::class, // 使用 Enum 转换
    ];

    /**
     * Appointment 属于一个 Staff
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}
