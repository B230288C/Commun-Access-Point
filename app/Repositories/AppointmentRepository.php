<?php

namespace App\Repositories;

use App\Models\Appointment;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class AppointmentRepository
{
    /**
     * Get all appointments with pagination and eager loading
     */
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return Appointment::with('staff')
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get single appointment by ID
     */
    public function findById(int $id): Appointment
    {
        return Appointment::with('staff')->findOrFail($id);
    }

    /**
     * Get appointments by staff ID
     */
    public function getByStaffId(int $staffId): Collection
    {
        return Appointment::where('staff_id', $staffId)
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();
    }

    /**
     * Get appointments by status
     */
    public function getByStatus(string $status): Collection
    {
        return Appointment::where('status', $status)
            ->with('staff')
            ->latest()
            ->get();
    }

    /**
     * Get upcoming appointments
     */
    public function getUpcoming(int $limit = 10): Collection
    {
        return Appointment::where('date', '>=', now()->toDateString())
            ->where('status', '!=', 'cancelled')
            ->with('staff')
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->limit($limit)
            ->get();
    }
}
