<?php

namespace App\Enums;

enum AvailabilitySlotStatus: string
{
    case Available = 'available';
    case Booked = 'booked';
    case Unavailable = 'unavailable';
}
