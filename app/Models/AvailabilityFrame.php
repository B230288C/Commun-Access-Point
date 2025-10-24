<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AvailabilityFrame extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id',
        'date',
        'day_of_week',
        'start_time',
        'end_time',
        'duration',
        'interval',
        'is_recurring',
        'repeat_group_id',
        'status',
    ];

    // 自动生成 repeat_group_id（仅当 is_recurring = true 且 repeat_group_id 为空）
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($frame) {
            if ($frame->is_recurring && empty($frame->repeat_group_id)) {
                $frame->repeat_group_id = (string) Str::uuid();
            }
        });
    }

    /**
     * 关联到 Staff（反向一对多）
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * 判断 frame 是否有效（例如未过期）
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
