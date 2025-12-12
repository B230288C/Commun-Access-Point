<?php

namespace App\Exceptions;

use Exception;

class FrameOverlapException extends Exception
{
    protected $overlappingFrames;

    public function __construct(string $message = 'Frame overlaps with existing frames', $overlappingFrames = [])
    {
        parent::__construct($message);
        $this->overlappingFrames = $overlappingFrames;
    }

    public function getOverlappingFrames()
    {
        return $this->overlappingFrames;
    }
}
