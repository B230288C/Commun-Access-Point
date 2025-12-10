<?php

namespace App\Factories;

use App\Models\AvailabilityFrame;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AvailabilityFrameFactory
{
    /**
     * Create a new availability frame.
     *
     * @param array $data
     * @return AvailabilityFrame
     * @throws \Exception
     */
    public static function create(array $data): AvailabilityFrame
    {
        try {
            return DB::transaction(function () use ($data) {
                $frame = AvailabilityFrame::create($data);

                Log::info('Availability frame created successfully', [
                    'frame_id' => $frame->id,
                    'staff_id' => $frame->staff_id,
                    'is_recurring' => $frame->is_recurring,
                    'repeat_group_id' => $frame->repeat_group_id,
                ]);

                return $frame;
            });
        } catch (\Exception $e) {
            Log::error('Failed to create availability frame', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            throw $e;
        }
    }

    /**
     * Update an existing availability frame.
     *
     * @param AvailabilityFrame $frame
     * @param array $data
     * @return AvailabilityFrame
     * @throws \Exception
     */
    public static function update(AvailabilityFrame $frame, array $data): AvailabilityFrame
    {
        try {
            return DB::transaction(function () use ($frame, $data) {
                $frame->update($data);

                Log::info('Availability frame updated successfully', [
                    'frame_id' => $frame->id,
                    'staff_id' => $frame->staff_id,
                ]);

                return $frame->fresh();
            });
        } catch (\Exception $e) {
            Log::error('Failed to update availability frame', [
                'frame_id' => $frame->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Delete an availability frame.
     *
     * @param AvailabilityFrame $frame
     * @return bool
     * @throws \Exception
     */
    public static function delete(AvailabilityFrame $frame): bool
    {
        try {
            return DB::transaction(function () use ($frame) {
                $frameId = $frame->id;
                $deleted = $frame->delete();

                Log::info('Availability frame deleted successfully', [
                    'frame_id' => $frameId,
                ]);

                return $deleted;
            });
        } catch (\Exception $e) {
            Log::error('Failed to delete availability frame', [
                'frame_id' => $frame->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Delete all frames in a recurring group.
     *
     * @param string $repeatGroupId
     * @return int Number of frames deleted
     * @throws \Exception
     */
    public static function deleteByRepeatGroup(string $repeatGroupId): int
    {
        try {
            return DB::transaction(function () use ($repeatGroupId) {
                $count = AvailabilityFrame::where('repeat_group_id', $repeatGroupId)->delete();

                Log::info('Recurring frames deleted successfully', [
                    'repeat_group_id' => $repeatGroupId,
                    'count' => $count,
                ]);

                return $count;
            });
        } catch (\Exception $e) {
            Log::error('Failed to delete recurring frames', [
                'repeat_group_id' => $repeatGroupId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
