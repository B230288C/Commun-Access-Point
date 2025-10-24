@extends('layouts.app')

@section('title', 'Appointment Details')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">Appointment Details</h1>
            <div class="space-x-2">
                <a href="{{ route('appointments.edit', $appointment->id) }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition">
                    Edit
                </a>
                <a href="{{ route('appointments.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition">
                    Back
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Status Card -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-2xl font-bold text-gray-900">Status</h2>
                        <span class="inline-flex items-center px-4 py-2 rounded-full text-lg font-semibold
                            @switch($appointment->status->value)
                                @case('pending')
                                    bg-yellow-100 text-yellow-800
                                @break
                                @case('approved')
                                    bg-green-100 text-green-800
                                @break
                                @case('completed')
                                    bg-blue-100 text-blue-800
                                @break
                                @case('cancelled')
                                    bg-red-100 text-red-800
                                @break
                            @endswitch
                        ">
                            {{ ucfirst($appointment->status->value) }}
                        </span>
                    </div>
                </div>

                <!-- Visitor Information -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Visitor Information</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Name</p>
                            <p class="text-lg font-medium text-gray-900">{{ $appointment->visitor_name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">NRIC/Passport</p>
                            <p class="text-lg font-medium text-gray-900">{{ $appointment->nric_passport }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Email</p>
                            <p class="text-lg font-medium text-gray-900">{{ $appointment->email }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Phone Number</p>
                            <p class="text-lg font-medium text-gray-900">{{ $appointment->phone_number }}</p>
                        </div>
                    </div>
                </div>

                <!-- Appointment Information -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Appointment Information</h3>
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <p class="text-sm text-gray-600">Date</p>
                            <p class="text-lg font-medium text-gray-900">{{ $appointment->date->format('M d, Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Time</p>
                            <p class="text-lg font-medium text-gray-900">{{ $appointment->start_time }} - {{ $appointment->end_time }}</p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <p class="text-sm text-gray-600">Purpose</p>
                        <p class="text-gray-900 whitespace-pre-line">{{ $appointment->purpose }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600">Person In Charge</p>
                        <p class="text-lg font-medium text-gray-900">{{ $appointment->personal_in_charge }}</p>
                    </div>
                </div>

                <!-- Staff Information -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Assigned Staff</h3>
                    @if ($appointment->staff)
                        <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                            <p class="text-sm text-gray-600">Name</p>
                            <p class="text-xl font-semibold text-gray-900">{{ $appointment->staff->name }}</p>

                            <div class="grid grid-cols-2 gap-4 mt-4">
                                <div>
                                    <p class="text-sm text-gray-600">Position</p>
                                    <p class="font-medium text-gray-900">{{ $appointment->staff->position }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Department</p>
                                    <p class="font-medium text-gray-900">{{ $appointment->staff->department }}</p>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-sm text-gray-600">Email</p>
                                    <p class="font-medium text-gray-900">{{ $appointment->staff->email }}</p>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-sm text-gray-600">Phone</p>
                                    <p class="font-medium text-gray-900">{{ $appointment->staff->phone }}</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="text-gray-600">No staff member assigned</p>
                    @endif
                </div>

                <!-- Timestamps -->
                <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-600">
                    <p>Created: {{ $appointment->created_at->format('M d, Y \a\t H:i') }}</p>
                    <p>Last Updated: {{ $appointment->updated_at->format('M d, Y \a\t H:i') }}</p>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-4">
                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>
                    <div class="space-y-2">
                        <a href="{{ route('appointments.edit', $appointment->id) }}" class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition">
                            Edit
                        </a>
                        <form action="{{ route('appointments.destroy', $appointment->id) }}" method="POST"
                              onsubmit="return confirm('Are you sure you want to delete this appointment?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Info Card -->
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                    <p class="text-sm text-gray-600 mb-2"><strong>ID:</strong> {{ $appointment->id }}</p>
                    <p class="text-sm text-gray-600"><strong>Reference:</strong> APT-{{ str_pad($appointment->id, 6, '0', STR_PAD_LEFT) }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
