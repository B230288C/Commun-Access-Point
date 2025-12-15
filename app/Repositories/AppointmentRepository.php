<?php

namespace App\Repositories;

use App\Models\Appointment;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class AppointmentRepository
{
    /**
     * Get filtered appointments with pagination for the API
     * Handles Search, Status filtering, and Staff ID
     */
    public function getFilteredList(array $filters, int $perPage = 5): LengthAwarePaginator
    {
        // Eager load relationships to avoid N+1 issues
        // We load 'availabilitySlot.availabilityFrame' to get time and date info
        $query = Appointment::with(['staff', 'availabilitySlot.availabilityFrame']);

        // 1. Filter by Staff ID (if provided)
        if (!empty($filters['user_id'])) {
            $query->where('staff_id', $filters['user_id']);
        }

        // 2. Filter by Status
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        // 3. Search (Visitor Name or Student Name)
        if (!empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term) {
                $q->where('visitor_name', 'LIKE', "%{$term}%")
                  ->orWhere('student_name', 'LIKE', "%{$term}%");
            });
        }

        // Return paginated results, sorted by newest first
        return $query->latest() // Equivalent to orderBy('created_at', 'desc')
                     ->paginate($perPage);
    }

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
     * Get appointments by staff ID with slot and frame info
     * (Note: This returns a Collection, mostly used for simple lists without pagination)
     */
    public function getByStaffId(int $staffId): Collection
    {
        return Appointment::where('staff_id', $staffId)
            ->with(['staff', 'availabilitySlot.availabilityFrame'])
            ->get()
            ->sortBy(function ($appointment) {
                $frame = $appointment->availabilitySlot?->availabilityFrame;
                $slot = $appointment->availabilitySlot;
                return ($frame?->date ?? '9999-99-99') . ' ' . ($slot?->start_time ?? '99:99:99');
            })
            ->values();
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