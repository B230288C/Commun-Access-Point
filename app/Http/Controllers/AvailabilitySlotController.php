<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAvailabilitySlotRequest;
use App\Http\Requests\UpdateAvailabilitySlotRequest;
use App\Repositories\AvailabilitySlotRepository;
use Illuminate\Http\JsonResponse;

class AvailabilitySlotController extends Controller
{
    protected $slotRepo;

    public function __construct(AvailabilitySlotRepository $slotRepo)
    {
        $this->slotRepo = $slotRepo;
    }

    /**
     * Get all slots
     */
    public function index(): JsonResponse
    {
        $slots = $this->slotRepo->getAll();
        return response()->json($slots);
    }

    /**
     * Store a new slot
     */
    public function store(StoreAvailabilitySlotRequest $request): JsonResponse
    {
        $data = $request->validated();
        $slot = $this->slotRepo->create($data);
        return response()->json($slot, 201);
    }

    /**
     * Show a single slot
     */
    public function show(int $id): JsonResponse
    {
        $slot = $this->slotRepo->findOrFail($id);
        return response()->json($slot);
    }

    /**
     * Update a slot
     */
    public function update(UpdateAvailabilitySlotRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();
        $slot = $this->slotRepo->update($id, $data);
        return response()->json($slot);
    }

    /**
     * Delete a slot
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->slotRepo->delete($id);
        if (!$deleted) {
            return response()->json(['message' => 'Slot cannot be deleted, it may have an appointment'], 400);
        }
        return response()->json(['message' => 'Slot deleted successfully']);
    }
}
