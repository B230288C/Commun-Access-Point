<?php

namespace App\Repositories;

use App\Models\Appointment;

class AppointmentRepository
{
    /**
     * 获取所有预约（可分页）
     */
    public function getAll($perPage = 10)
    {
        return Appointment::with('staff')->latest()->paginate($perPage);
    }

    /**
     * 根据ID获取单个预约
     */
    public function findById($id)
    {
        return Appointment::with('staff')->findOrFail($id);
    }

    /**
     * 创建新预约
     */
    public function create(array $data)
    {
        return Appointment::create($data);
    }

    /**
     * 更新预约
     */
    public function update($id, array $data)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->update($data);
        return $appointment;
    }

    /**
     * 删除预约
     */
    public function delete($id)
    {
        $appointment = Appointment::findOrFail($id);
        return $appointment->delete();
    }

    /**
     * 根据 Staff ID 获取所有预约
     */
    public function getByStaffId($staffId)
    {
        return Appointment::where('staff_id', $staffId)
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();
    }

    /**
     * 获取指定状态的预约
     */
    public function getByStatus($status)
    {
        return Appointment::where('status', $status)->get();
    }
}
