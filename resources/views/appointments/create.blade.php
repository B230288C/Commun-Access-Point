@extends('layouts.app')

@section('title', 'Create Appointment')

@section('content')
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Create New Appointment</h1>

        <div class="bg-white rounded-lg shadow-sm p-8">
            <form action="{{ route('appointments.store') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Visitor Information Section -->
                <fieldset class="border-b pb-6">
                    <legend class="text-lg font-semibold text-gray-900 mb-4">Visitor Information</legend>

                    <!-- Visitor Name -->
                    <div class="mb-4">
                        <label for="visitor_name" class="block text-sm font-medium text-gray-700 mb-1">
                            Visitor Name <span class="text-red-600">*</span>
                        </label>
                        <input type="text" id="visitor_name" name="visitor_name"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('visitor_name') border-red-500 @enderror"
                               value="{{ old('visitor_name') }}" placeholder="John Doe">
                        @error('visitor_name')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- NRIC/Passport -->
                    <div class="mb-4">
                        <label for="nric_passport" class="block text-sm font-medium text-gray-700 mb-1">
                            NRIC/Passport <span class="text-red-600">*</span>
                        </label>
                        <input type="text" id="nric_passport" name="nric_passport"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('nric_passport') border-red-500 @enderror"
                               value="{{ old('nric_passport') }}" placeholder="S1234567D">
                        @error('nric_passport')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Phone Number -->
                    <div class="mb-4">
                        <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-1">
                            Phone Number <span class="text-red-600">*</span>
                        </label>
                        <input type="tel" id="phone_number" name="phone_number"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('phone_number') border-red-500 @enderror"
                               value="{{ old('phone_number') }}" placeholder="+60123456789">
                        @error('phone_number')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            Email <span class="text-red-600">*</span>
                        </label>
                        <input type="email" id="email" name="email"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-500 @enderror"
                               value="{{ old('email') }}" placeholder="john@example.com">
                        @error('email')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </fieldset>

                <!-- Appointment Details Section -->
                <fieldset class="border-b pb-6">
                    <legend class="text-lg font-semibold text-gray-900 mb-4">Appointment Details</legend>

                    <!-- Staff Member -->
                    <div class="mb-4">
                        <label for="staff_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Staff Member <span class="text-red-600">*</span>
                        </label>
                        <select id="staff_id" name="staff_id"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('staff_id') border-red-500 @enderror">
                            <option value="">-- Select Staff Member --</option>
                            @foreach ($staff as $member)
                                <option value="{{ $member->id }}" @selected(old('staff_id') == $member->id)>
                                    {{ $member->name }} - {{ $member->position }}
                                </option>
                            @endforeach
                        </select>
                        @error('staff_id')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Purpose -->
                    <div class="mb-4">
                        <label for="purpose" class="block text-sm font-medium text-gray-700 mb-1">
                            Purpose <span class="text-red-600">*</span>
                        </label>
                        <textarea id="purpose" name="purpose" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('purpose') border-red-500 @enderror"
                                  placeholder="Reason for visit">{{ old('purpose') }}</textarea>
                        @error('purpose')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Personal In Charge -->
                    <div class="mb-4">
                        <label for="personal_in_charge" class="block text-sm font-medium text-gray-700 mb-1">
                            Person In Charge <span class="text-red-600">*</span>
                        </label>
                        <input type="text" id="personal_in_charge" name="personal_in_charge"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('personal_in_charge') border-red-500 @enderror"
                               value="{{ old('personal_in_charge') }}" placeholder="Department Manager">
                        @error('personal_in_charge')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Date -->
                    <div class="mb-4">
                        <label for="date" class="block text-sm font-medium text-gray-700 mb-1">
                            Appointment Date <span class="text-red-600">*</span>
                        </label>
                        <input type="date" id="date" name="date"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('date') border-red-500 @enderror"
                               value="{{ old('date') }}" min="{{ today()->toDateString() }}">
                        @error('date')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Time Range -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="start_time" class="block text-sm font-medium text-gray-700 mb-1">
                                Start Time <span class="text-red-600">*</span>
                            </label>
                            <input type="time" id="start_time" name="start_time"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('start_time') border-red-500 @enderror"
                                   value="{{ old('start_time') }}">
                            @error('start_time')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="end_time" class="block text-sm font-medium text-gray-700 mb-1">
                                End Time <span class="text-red-600">*</span>
                            </label>
                            <input type="time" id="end_time" name="end_time"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('end_time') border-red-500 @enderror"
                                   value="{{ old('end_time') }}">
                            @error('end_time')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </fieldset>

                <!-- Form Actions -->
                <div class="flex gap-4">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition">
                        Create Appointment
                    </button>
                    <a href="{{ route('appointments.index') }}" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-900 font-bold py-2 px-4 rounded-lg transition text-center">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
