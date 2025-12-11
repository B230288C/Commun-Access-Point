<?php

namespace App\Repositories;

use App\Models\AvailabilityFrame;

class AvailabilityFrameRepository
{
    /**
     * 获取所有可用的 frame（可用于 staff 的管理页面）
     */
    public function getAll()
    {
        return AvailabilityFrame::orderBy('created_at', 'desc')->get();
    }

    /**
     * 根据 staff_id 获取该员工的所有 frame（含 slots）
     */
    public function getByStaff(int $staffId)
    {
        return AvailabilityFrame::with('availabilitySlots')
            ->where('staff_id', $staffId)
            ->orderBy('date', 'asc')
            ->get();
    }

    /**
     * 根据 ID 获取单个 frame
     */
    public function findById(int $id): AvailabilityFrame
    {
        return AvailabilityFrame::findOrFail($id);
    }

    /**
     * 创建新的 frame
     */
    public function create(array $data): AvailabilityFrame
    {
        return AvailabilityFrame::create($data);
    }

    /**
     * 更新 frame
     */
    public function update(int $id, array $data): AvailabilityFrame
    {
        $frame = $this->findById($id);
        $frame->update($data);
        return $frame;
    }

    /**
     * 删除单个 frame
     */
    public function delete(int $id): bool
    {
        $frame = $this->findById($id);
        return $frame->delete();
    }

    /**
     * 批量删除同组的 recurring frames
     */
    public function deleteByRepeatGroup(string $repeatGroupId): int
    {
        return AvailabilityFrame::where('repeat_group_id', $repeatGroupId)->delete();
    }
}
