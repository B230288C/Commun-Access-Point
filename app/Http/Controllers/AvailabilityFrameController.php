<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\AvailabilityFrameRepository;
use App\Enums\AvailabilityFrameStatus;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AvailabilityFrameController extends Controller
{
    protected $frameRepo;

    public function __construct(AvailabilityFrameRepository $frameRepo)
    {
        $this->frameRepo = $frameRepo;
    }

    /**
     * 获取所有 frame（后台查看）
     */
    public function index()
    {
        $frames = $this->frameRepo->getAll();
        return response()->json($frames);
    }

    /**
     * 根据 staff_id 获取该员工的所有 frame
     */
    public function getByStaff($staffId)
    {
        $frames = $this->frameRepo->getByStaff($staffId);
        return response()->json($frames);
    }

    /**
     * 获取单个 frame 详情
     */
    public function show($id)
    {
        $frame = $this->frameRepo->findById($id);

        if (!$frame) {
            return response()->json(['message' => 'Availability frame not found'], 404);
        }

        return response()->json($frame);
    }

    /**
     * 创建新的 frame
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'staff_id' => 'required|integer',
            'date' => 'nullable|date',
            'day_of_week' => 'nullable|string',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'duration' => 'required|integer|min:5',
            'interval' => 'nullable|integer|min:0',
            'is_recurring' => 'boolean',
            'repeat_group_id' => 'nullable|uuid',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        $frame = $this->frameRepo->create($data);

        return response()->json([
            'message' => 'Availability frame created successfully',
            'data' => $frame
        ], 201);
    }

    /**
     * 更新 frame
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'date' => 'nullable|date',
            'day_of_week' => 'nullable|string',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'duration' => 'nullable|integer|min:5',
            'interval' => 'nullable|integer|min:0',
            'is_recurring' => 'boolean',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        try {
            $frame = $this->frameRepo->update($id, $data);
            return response()->json([
                'message' => 'Availability frame updated successfully',
                'data' => $frame
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Availability frame not found'], 404);
        }
    }

    /**
     * 删除单个 frame
     */
    public function destroy($id)
    {
        try {
            $this->frameRepo->delete($id);
            return response()->json(['message' => 'Availability frame deleted successfully']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Availability frame not found'], 404);
        }
    }

    /**
     * 批量删除同组 recurring frame
     */
    public function deleteByRepeatGroup($repeatGroupId)
    {
        $count = $this->frameRepo->deleteByRepeatGroup($repeatGroupId);

        if ($count === 0) {
            return response()->json(['message' => 'No frames found for the given repeat group'], 404);
        }

        return response()->json([
            'message' => "Deleted {$count} recurring frames successfully"
        ]);
    }
}
