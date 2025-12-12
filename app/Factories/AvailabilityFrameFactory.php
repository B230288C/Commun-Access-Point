<?php

namespace App\Factories;

use App\Enums\AvailabilitySlotStatus;
use App\Exceptions\FrameOverlapException;
use App\Models\AvailabilityFrame;
use App\Models\AvailabilitySlot;
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
     * @throws FrameOverlapException
     * @throws \Exception
     */
    public static function create(array $data): AvailabilityFrame
    {
        try {
            return DB::transaction(function () use ($data) {
                // Check for overlaps before creating
                if (!empty($data['date'])) {
                    self::validateNoOverlap(
                        $data['staff_id'],
                        $data['date'],
                        $data['start_time'],
                        $data['end_time']
                    );
                }

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

                // Generate slots for the original frame
                self::generateSlots($frame);

                // If recurring, create instances for next weeks
                if ($frame->is_recurring && $repeatGroupId) {
                    self::createRecurringInstances($frame, $repeatGroupId);
                }

                return $frame;
            });
        } catch (FrameOverlapException $e) {
            throw $e;
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

        for ($week = 1; $week <= self::RECURRING_WEEKS; $week++) {
            $instanceDate = $startDate->copy()->addWeeks($week);

            $instance = AvailabilityFrame::create([
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
            ]);

            // Generate slots for each recurring instance
            self::generateSlots($instance);
        }

        Log::info('Recurring instances created with slots', [
            'repeat_group_id' => $repeatGroupId,
            'count' => self::RECURRING_WEEKS,
        ]);
    }

    /**
     * Update an existing availability frame.
     * Handles recurring state changes:
     * - If changing to recurring: creates instances for next 52 weeks
     * - If changing to non-recurring: deletes future instances in the group (keeps past)
     * - Title/other updates only affect the single instance (not all recurring)
     *
     * Also regenerates slots when time-related fields change.
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

                // Track if time-related fields are changing (for slot regeneration)
                $needsSlotRegeneration = self::timeFieldsChanged($frame, $data);

                // Case 1: Changing from non-recurring to recurring
                if (!$wasRecurring && $willBeRecurring) {
                    $repeatGroupId = (string) Str::uuid();
                    $data['repeat_group_id'] = $repeatGroupId;

                    $frame->update($data);

                    // Regenerate slots for this frame if time fields changed
                    if ($needsSlotRegeneration) {
                        self::regenerateSlots($frame);
                    }

                    // Create recurring instances (includes slot generation)
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

                    // Regenerate slots if time fields changed
                    if ($needsSlotRegeneration) {
                        self::regenerateSlots($frame);
                    }
                }
                // Case 3: Still recurring - only update this single instance
                elseif ($wasRecurring && $willBeRecurring) {
                    // Only update this specific frame, not all instances
                    $frame->update($data);

                    // Regenerate slots if time fields changed
                    if ($needsSlotRegeneration) {
                        self::regenerateSlots($frame);
                    }

                    Log::info('Updated single recurring instance', [
                        'frame_id' => $frame->id,
                        'repeat_group_id' => $oldRepeatGroupId,
                    ]);
                }
                // Case 4: Not recurring, just update normally
                else {
                    $frame->update($data);

                    // Regenerate slots if time fields changed
                    if ($needsSlotRegeneration) {
                        self::regenerateSlots($frame);
                    }
                }

                Log::info('Availability frame updated successfully', [
                    'frame_id' => $frame->id,
                    'staff_id' => $frame->staff_id,
                    'was_recurring' => $wasRecurring,
                    'is_recurring' => $willBeRecurring,
                    'slots_regenerated' => $needsSlotRegeneration,
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
     * Check if time-related fields are changing.
     *
     * @param AvailabilityFrame $frame
     * @param array $data
     * @return bool
     */
    private static function timeFieldsChanged(AvailabilityFrame $frame, array $data): bool
    {
        $timeFields = ['start_time', 'end_time', 'duration', 'interval', 'date'];

        foreach ($timeFields as $field) {
            if (isset($data[$field]) && $data[$field] != $frame->{$field}) {
                return true;
            }
        }

        return false;
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

    /**
     * Generate availability slots for a frame based on its duration and interval.
     *
     * Slots are generated from start_time to end_time using:
     * - duration: length of each slot in minutes
     * - interval: gap between slots in minutes
     *
     * Example: Frame 09:00-14:00, duration=20, interval=10
     * Generates: 09:00-09:20, 09:30-09:50, 10:00-10:20, ...
     *
     * @param AvailabilityFrame $frame
     * @return void
     */
    public static function generateSlots(AvailabilityFrame $frame): void
    {
        $startTime = Carbon::parse($frame->start_time);
        $endTime = Carbon::parse($frame->end_time);

        $duration = (int) $frame->duration;
        $interval = (int) $frame->interval;

        $slots = [];
        $slotCount = 0;
        $currentStart = $startTime->copy();

        while ($currentStart->copy()->addMinutes($duration)->lte($endTime)) {
            $slotEnd = $currentStart->copy()->addMinutes($duration);

            $slots[] = [
                'availability_frame_id' => $frame->id,
                'start_time' => $currentStart->format('H:i:s'),
                'end_time' => $slotEnd->format('H:i:s'),
                'status' => AvailabilitySlotStatus::Available->value,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $slotCount++;

            // Move to next slot: current end + interval
            $currentStart = $slotEnd->addMinutes($interval);
        }

        // Bulk insert for performance
        if (!empty($slots)) {
            AvailabilitySlot::insert($slots);

            Log::info('Slots generated for frame', [
                'frame_id' => $frame->id,
                'slot_count' => $slotCount,
            ]);
        }
    }

    /**
     * Regenerate slots for a frame.
     * Deletes existing slots (without appointments) and creates new ones.
     *
     * @param AvailabilityFrame $frame
     * @return void
     */
    public static function regenerateSlots(AvailabilityFrame $frame): void
    {
        // Delete existing slots that don't have appointments
        AvailabilitySlot::where('availability_frame_id', $frame->id)
            ->whereDoesntHave('appointment')
            ->delete();

        // Generate new slots
        self::generateSlots($frame);

        Log::info('Slots regenerated for frame', [
            'frame_id' => $frame->id,
        ]);
    }

    /**
     * Move a frame and all its slots by a time delta.
     * Updates start_time and end_time for both the frame and all associated slots.
     * Optionally updates the date if provided.
     *
     * @param AvailabilityFrame $frame
     * @param int $deltaMinutes Time difference in minutes (positive or negative)
     * @param string|null $newDate New date for the frame (Y-m-d format)
     * @return AvailabilityFrame
     * @throws FrameOverlapException
     * @throws \Exception
     */
    public static function move(AvailabilityFrame $frame, int $deltaMinutes, ?string $newDate = null): AvailabilityFrame
    {
        try {
            return DB::transaction(function () use ($frame, $deltaMinutes, $newDate) {
                // Calculate new frame times
                $frameStart = Carbon::parse($frame->start_time);
                $frameEnd = Carbon::parse($frame->end_time);

                $newFrameStart = $frameStart->copy()->addMinutes($deltaMinutes);
                $newFrameEnd = $frameEnd->copy()->addMinutes($deltaMinutes);

                // Determine the target date
                $targetDate = $newDate ?? $frame->date;

                // Validate no overlap at the new position (exclude current frame)
                self::validateNoOverlap(
                    $frame->staff_id,
                    $targetDate,
                    $newFrameStart->format('H:i:s'),
                    $newFrameEnd->format('H:i:s'),
                    $frame->id
                );

                // Prepare update data for frame
                $frameUpdateData = [
                    'start_time' => $newFrameStart->format('H:i:s'),
                    'end_time' => $newFrameEnd->format('H:i:s'),
                ];

                // Update date if provided
                if ($newDate !== null) {
                    $frameUpdateData['date'] = $newDate;
                    // Update day name based on new date
                    $frameUpdateData['day'] = Carbon::parse($newDate)->format('l');
                }

                $frame->update($frameUpdateData);

                // Update all associated slots with the same delta
                $slots = AvailabilitySlot::where('availability_frame_id', $frame->id)->get();

                foreach ($slots as $slot) {
                    $slotStart = Carbon::parse($slot->start_time);
                    $slotEnd = Carbon::parse($slot->end_time);

                    $slot->update([
                        'start_time' => $slotStart->addMinutes($deltaMinutes)->format('H:i:s'),
                        'end_time' => $slotEnd->addMinutes($deltaMinutes)->format('H:i:s'),
                    ]);
                }

                Log::info('Frame and slots moved successfully', [
                    'frame_id' => $frame->id,
                    'delta_minutes' => $deltaMinutes,
                    'new_date' => $newDate,
                    'slots_updated' => $slots->count(),
                ]);

                return $frame->fresh()->load('availabilitySlots');
            });
        } catch (FrameOverlapException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Failed to move availability frame', [
                'frame_id' => $frame->id,
                'delta_minutes' => $deltaMinutes,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Validate that a frame does not overlap with existing frames.
     * Uses strict inequalities: (ExistingStart < NewEnd) AND (ExistingEnd > NewStart)
     *
     * @param int $staffId
     * @param string $date
     * @param string $startTime
     * @param string $endTime
     * @param int|null $excludeFrameId Frame ID to exclude (for updates/moves)
     * @throws FrameOverlapException
     */
    private static function validateNoOverlap(int $staffId, string $date, string $startTime, string $endTime, ?int $excludeFrameId = null): void
    {
        $query = AvailabilityFrame::where('staff_id', $staffId)
            ->where('date', $date)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime);

        if ($excludeFrameId !== null) {
            $query->where('id', '!=', $excludeFrameId);
        }

        $overlappingFrames = $query->get();

        if ($overlappingFrames->isNotEmpty()) {
            $frameInfo = $overlappingFrames->map(function ($frame) {
                return "{$frame->title} ({$frame->start_time} - {$frame->end_time})";
            })->implode(', ');

            throw new FrameOverlapException(
                "Frame overlaps with existing frames: {$frameInfo}",
                $overlappingFrames->toArray()
            );
        }
    }
}
