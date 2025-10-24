@extends('layouts.app')

@section('title', 'All Appointments')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-900">Appointments</h1>
        <a href="{{ route('appointments.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition">
            + New Appointment
        </a>
    </div>

    <!-- Search and Filter -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <form action="{{ route('appointments.index') }}" method="GET" class="flex gap-4">
            <input type="text" name="search" placeholder="Search by visitor name..."
                   class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   value="{{ request('search') }}">
            <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition">
                Search
            </button>
        </form>
    </div>

    @if ($appointments->count() > 0)
        <!-- Appointments Table -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Visitor</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Date & Time</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Staff</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Purpose</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                        <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach ($appointments as $appointment)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">{{ $appointment->visitor_name }}</div>
                                <div class="text-sm text-gray-500">{{ $appointment->email }}</div>
                            </td>
                            <td class="px-6 py-4 text-gray-700">
                                <div>{{ $appointment->date->format('M d, Y') }}</div>
                                <div class="text-sm text-gray-500">{{ $appointment->start_time }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">{{ $appointment->staff->name ?? 'N/A' }}</div>
                                <div class="text-sm text-gray-500">{{ $appointment->staff->position ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4 text-gray-700 text-sm">{{ $appointment->purpose }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
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
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('appointments.show', $appointment->id) }}"
                                   class="text-blue-600 hover:text-blue-900 font-medium text-sm">View</a>
                                <a href="{{ route('appointments.edit', $appointment->id) }}"
                                   class="text-green-600 hover:text-green-900 font-medium text-sm">Edit</a>
                                <form action="{{ route('appointments.destroy', $appointment->id) }}" method="POST"
                                      class="inline" onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 font-medium text-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $appointments->links() }}
        </div>
    @else
        <div class="bg-white rounded-lg shadow-sm p-12 text-center">
            <p class="text-gray-500 text-lg">No appointments found.</p>
            <a href="{{ route('appointments.create') }}" class="text-blue-600 hover:text-blue-900 font-medium mt-4 inline-block">
                Create your first appointment →
            </a>
        </div>
    @endif
@endsection
