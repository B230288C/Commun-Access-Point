<?php

namespace App\Http\Controllers;

use App\Factories\AvailabilityFrameFactory;
use App\Http\Requests\AvailabilityFrameRequest;
use App\Http\Requests\CreateAvailabilityFrameRequest;
use App\Http\Resources\AvailabilityFrameResource;
use App\Repositories\AvailabilityFrameRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

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
    public function index(): JsonResponse
    {
        $frames = $this->frameRepo->getAll();
        return response()->json($frames);
    }

    /**
     * 根据 staff_id 获取该员工的所有 frame
     */
    public function getByStaff($staffId): JsonResponse
    {
        $frames = $this->frameRepo->getByStaff($staffId);
        return response()->json($frames);
    }

    /**
     * 获取单个 frame 详情
     */
    public function show($id): JsonResponse
    {
        try {
            $frame = $this->frameRepo->findById($id);
            return response()->json($frame);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Availability frame not found'], 404);
        }
    }

    /**
     * 创建新的 frame
     */
    public function store(CreateAvailabilityFrameRequest $request): JsonResponse
    {
        try {
            $frame = AvailabilityFrameFactory::create($request->validated());

            return response()->json([
                'message' => 'Availability frame created successfully',
                'data' => new AvailabilityFrameResource($frame),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create availability frame',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 更新 frame
     */
    public function update(AvailabilityFrameRequest $request, $id): JsonResponse
    {
        try {
            $frame = $this->frameRepo->findById($id);
            $updatedFrame = AvailabilityFrameFactory::update($frame, $request->validated());

            return response()->json([
                'message' => 'Availability frame updated successfully',
                'data' => new AvailabilityFrameResource($updatedFrame)
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Availability frame not found'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update availability frame',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 删除单个 frame
     */
    public function destroy($id): JsonResponse
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
    public function deleteByRepeatGroup($repeatGroupId): JsonResponse
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
