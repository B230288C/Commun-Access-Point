<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Staff extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'department',
        'position',
    ];

    /**
     * Staff has many appointments
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
