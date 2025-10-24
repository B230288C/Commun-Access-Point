<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Staff;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Repositories\AppointmentRepository;
use App\Factories\AppointmentFactory;

class AppointmentController extends Controller
{
    protected $appointmentRepo;

    public function __construct(AppointmentRepository $appointmentRepo)
    {
        $this->appointmentRepo = $appointmentRepo;
    }

    /**
     * GET /appointments
     * Display list of all appointments with pagination
     */
    public function index()
    {
        $appointments = $this->appointmentRepo->getAll(perPage: 15);
        return view('appointments.index', compact('appointments'));
    }

    /**
     * GET /appointments/create
     * Display create appointment form
     */
    public function create()
    {
        $staff = Staff::all();
        return view('appointments.create', compact('staff'));
    }

    /**
     * POST /appointments
     * Store appointment using AppointmentFactory
     */
    public function store(StoreAppointmentRequest $request)
    {
        $appointment = AppointmentFactory::create($request->validated());
        return redirect()->route('appointments.show', $appointment->id)
            ->with('success', 'Appointment created successfully');
    }

    /**
     * GET /appointments/{id}
     * Display single appointment details
     */
    public function show(Appointment $appointment)
    {
        $appointment->load('staff');
        return view('appointments.show', compact('appointment'));
    }

    /**
     * GET /appointments/{id}/edit
     * Display edit appointment form
     */
    public function edit(Appointment $appointment)
    {
        $staff = Staff::all();
        return view('appointments.edit', compact('appointment', 'staff'));
    }

    /**
     * PUT /appointments/{id}
     * Update appointment using AppointmentFactory
     */
    public function update(UpdateAppointmentRequest $request, Appointment $appointment)
    {
        $appointment = AppointmentFactory::update($appointment, $request->validated());
        return redirect()->route('appointments.show', $appointment->id)
            ->with('success', 'Appointment updated successfully');
    }

    /**
     * DELETE /appointments/{id}
     * Delete appointment using AppointmentFactory
     */
    public function destroy(Appointment $appointment)
    {
        AppointmentFactory::delete($appointment);
        return redirect()->route('appointments.index')
            ->with('success', 'Appointment deleted successfully');
    }
}
