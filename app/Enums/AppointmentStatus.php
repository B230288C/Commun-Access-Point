<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case Pending = 'Pending';
    case Approved = 'Approved';
    case Cancelled = 'Cancelled';
    case Completed = 'Completed';
}
