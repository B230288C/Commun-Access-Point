<?php

namespace App\Exceptions;

use Exception;

class SlotOverlapException extends Exception
{
    protected $overlappingSlots;

    public function __construct(string $message = 'Slot overlaps with existing slots', $overlappingSlots = [])
    {
        parent::__construct($message);
        $this->overlappingSlots = $overlappingSlots;
    }

    public function getOverlappingSlots()
    {
        return $this->overlappingSlots;
    }
}
