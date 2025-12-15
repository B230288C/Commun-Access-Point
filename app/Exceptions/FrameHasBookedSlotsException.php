<?php

namespace App\Exceptions;

use Exception;

class FrameHasBookedSlotsException extends Exception
{
    protected $bookedSlots;

    public function __construct(string $message = 'Cannot move frame with booked slots', $bookedSlots = [])
    {
        parent::__construct($message);
        $this->bookedSlots = $bookedSlots;
    }

    public function getBookedSlots()
    {
        return $this->bookedSlots;
    }
}
