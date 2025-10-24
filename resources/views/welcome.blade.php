<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <h1 class="text-5xl font-bold text-gray-900 mb-6">Appointment System</h1>
            <p class="text-xl text-gray-600 mb-12">Manage your appointments efficiently and effectively</p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('appointments.index') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg transition">
                    View All Appointments
                </a>
                <a href="{{ route('appointments.create') }}" class="inline-block bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-lg transition">
                    Create New Appointment
                </a>
            </div>

            <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Easy Scheduling</h3>
                    <p class="text-gray-600">Schedule appointments with staff members in just a few clicks</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Track Status</h3>
                    <p class="text-gray-600">Monitor appointment status from pending to completed</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Full Control</h3>
                    <p class="text-gray-600">Edit, update, or cancel appointments anytime</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
