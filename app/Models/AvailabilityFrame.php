<?php

namespace App\Models;

use App\Enums\AvailabilityFrameStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AvailabilityFrame extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id',
        'date',
        'title',
        'day',
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
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function availabilitySlots(): HasMany
    {
        return $this->hasMany(AvailabilitySlot::class);
    }

    /**
     * 判断 frame 是否有效
     */
    public function isActive(): bool
    {
        return $this->status === AvailabilityFrameStatus::Active;
    }
}
