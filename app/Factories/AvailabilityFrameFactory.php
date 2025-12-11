<?php

namespace App\Factories;

use App\Models\AvailabilityFrame;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AvailabilityFrameFactory
{
    /**
     * Number of weeks to generate recurring instances for.
     */
    private const RECURRING_WEEKS = 52;

    /**
     * Create a new availability frame.
     * If is_recurring is true, also creates instances for the next 52 weeks.
     *
     * @param array $data
     * @return AvailabilityFrame
     * @throws \Exception
     */
    public static function create(array $data): AvailabilityFrame
    {
        try {
            return DB::transaction(function () use ($data) {
                // Generate repeat_group_id if recurring
                $repeatGroupId = null;
                if (!empty($data['is_recurring'])) {
                    $repeatGroupId = $data['repeat_group_id'] ?? (string) Str::uuid();
                    $data['repeat_group_id'] = $repeatGroupId;
                }

                // Create the original frame
                $frame = AvailabilityFrame::create($data);

                Log::info('Availability frame created successfully', [
                    'frame_id' => $frame->id,
                    'staff_id' => $frame->staff_id,
                    'is_recurring' => $frame->is_recurring,
                    'repeat_group_id' => $frame->repeat_group_id,
                ]);

                // If recurring, create instances for next weeks
                if ($frame->is_recurring && $repeatGroupId) {
                    self::createRecurringInstances($frame, $repeatGroupId);
                }

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
     * Create recurring instances for the next N weeks.
     *
     * @param AvailabilityFrame $originalFrame
     * @param string $repeatGroupId
     * @return void
     */
    private static function createRecurringInstances(AvailabilityFrame $originalFrame, string $repeatGroupId): void
    {
        $startDate = Carbon::parse($originalFrame->date);
        $instances = [];

        for ($week = 1; $week <= self::RECURRING_WEEKS; $week++) {
            $instanceDate = $startDate->copy()->addWeeks($week);

            $instances[] = [
                'staff_id' => $originalFrame->staff_id,
                'date' => $instanceDate->format('Y-m-d'),
                'title' => $originalFrame->title,
                'day' => $originalFrame->day,
                'start_time' => $originalFrame->start_time,
                'end_time' => $originalFrame->end_time,
                'duration' => $originalFrame->duration,
                'interval' => $originalFrame->interval,
                'is_recurring' => true,
                'repeat_group_id' => $repeatGroupId,
                'status' => $originalFrame->status,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Bulk insert for performance
        AvailabilityFrame::insert($instances);

        Log::info('Recurring instances created', [
            'repeat_group_id' => $repeatGroupId,
            'count' => count($instances),
        ]);
    }

    /**
     * Update an existing availability frame.
     * Handles recurring state changes:
     * - If changing to recurring: creates instances for next 52 weeks
     * - If changing to non-recurring: deletes future instances in the group (keeps past)
     * - Title/other updates only affect the single instance (not all recurring)
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
                $wasRecurring = $frame->is_recurring;
                $willBeRecurring = $data['is_recurring'] ?? $wasRecurring;
                $oldRepeatGroupId = $frame->repeat_group_id;

                // Case 1: Changing from non-recurring to recurring
                if (!$wasRecurring && $willBeRecurring) {
                    $repeatGroupId = (string) Str::uuid();
                    $data['repeat_group_id'] = $repeatGroupId;

                    $frame->update($data);

                    // Create recurring instances
                    self::createRecurringInstances($frame, $repeatGroupId);

                    Log::info('Frame changed to recurring, instances created', [
                        'frame_id' => $frame->id,
                        'repeat_group_id' => $repeatGroupId,
                    ]);
                }
                // Case 2: Changing from recurring to non-recurring
                elseif ($wasRecurring && !$willBeRecurring) {
                    // Delete only FUTURE instances in the group (keep past occurrences)
                    if ($oldRepeatGroupId) {
                        $frameDate = Carbon::parse($frame->date);
                        $deletedCount = AvailabilityFrame::where('repeat_group_id', $oldRepeatGroupId)
                            ->where('id', '!=', $frame->id)
                            ->where('date', '>', $frameDate->format('Y-m-d'))
                            ->delete();

                        // Also remove repeat_group_id from past instances so they become standalone
                        AvailabilityFrame::where('repeat_group_id', $oldRepeatGroupId)
                            ->where('id', '!=', $frame->id)
                            ->update([
                                'repeat_group_id' => null,
                                'is_recurring' => false,
                            ]);

                        Log::info('Future recurring instances deleted, past instances kept', [
                            'frame_id' => $frame->id,
                            'frame_date' => $frame->date,
                            'repeat_group_id' => $oldRepeatGroupId,
                            'deleted_count' => $deletedCount,
                        ]);
                    }

                    $data['repeat_group_id'] = null;
                    $frame->update($data);
                }
                // Case 3: Still recurring - only update this single instance
                elseif ($wasRecurring && $willBeRecurring) {
                    // Only update this specific frame, not all instances
                    $frame->update($data);

                    Log::info('Updated single recurring instance', [
                        'frame_id' => $frame->id,
                        'repeat_group_id' => $oldRepeatGroupId,
                    ]);
                }
                // Case 4: Not recurring, just update normally
                else {
                    $frame->update($data);
                }

                Log::info('Availability frame updated successfully', [
                    'frame_id' => $frame->id,
                    'staff_id' => $frame->staff_id,
                    'was_recurring' => $wasRecurring,
                    'is_recurring' => $willBeRecurring,
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
