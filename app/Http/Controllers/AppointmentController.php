<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Repositories\AppointmentRepository;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    protected $appointmentRepo;

    public function __construct(AppointmentRepository $appointmentRepo)
    {
        $this->appointmentRepo = $appointmentRepo;
    }

    // 创建预约
    public function store(StoreAppointmentRequest $request)
    {
        $data = $request->validated();
        $appointment = $this->appointmentRepo->create($data);
        return response()->json($appointment, 201);
    }

    // 查看单个预约
    public function show($id)
    {
        $appointment = $this->appointmentRepo->find($id);
        if (!$appointment) {
            return response()->json(['message' => 'Appointment not found'], 404);
        }
        return response()->json($appointment);
    }

    // 更新预约
    public function update(UpdateAppointmentRequest $request, $id)
    {
        $data = $request->validated();
        $appointment = $this->appointmentRepo->update($id, $data);
        if (!$appointment) {
            return response()->json(['message' => 'Appointment not found'], 404);
        }
        return response()->json($appointment);
    }

    // 取消预约
    public function cancel($id)
    {
        $appointment = $this->appointmentRepo->find($id);
        if (!$appointment) {
            return response()->json(['message' => 'Appointment not found'], 404);
        }

        $appointment->status = \App\Enums\AppointmentStatus::Cancelled;
        $appointment->save();

        return response()->json(['message' => 'Appointment cancelled', 'appointment' => $appointment]);
    }
}
