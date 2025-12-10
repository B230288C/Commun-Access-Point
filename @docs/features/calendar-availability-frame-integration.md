# Calendar Availability Frame Integration

This document describes the complete integration of calendar drag/resize interactions with availability frame creation.

## Overview

When staff members interact with the calendar (drag to select, resize, or move events), the system captures the final datetime and displays a form for them to configure the availability frame details. The frame is only saved to the backend after explicit confirmation.

## Flow Diagram

```
1. User drags/resizes on calendar
   ↓
2. Capture start/end datetime
   ↓
3. Show modal with pre-filled date/time (read-only)
   ↓
4. User fills in: title, duration, interval, is_recurring, etc.
   ↓
5. User clicks "Create Frame"
   ↓
6. POST to /api/availability-frames
   ↓
7. On success: close modal, add frame to calendar
```

## Frontend Integration

### 1. Calendar Component (AppointmentCalendar.jsx)

The calendar captures three types of interactions:

#### A. Selection (Drag to create)
```javascript
const handleSelect = (selectInfo) => {
    const { start, end } = selectInfo;

    // Format date and times
    const date = start.toISOString().split('T')[0];
    const startTime = start.toTimeString().split(' ')[0].substring(0, 5);
    const endTime = end.toTimeString().split(' ')[0].substring(0, 5);
    const dayOfWeek = start.toLocaleDateString('en-US', { weekday: 'long' });

    // Open modal with pre-filled data
    setModalInitialData({
        date,
        start_time: startTime,
        end_time: endTime,
        day_of_week: dayOfWeek,
    });
    setIsModalOpen(true);
};
```

#### B. Event Resize
```javascript
const handleEventResize = (resizeInfo) => {
    const { event } = resizeInfo;
    const { start, end } = event;

    // Extract datetime
    const date = start.toISOString().split('T')[0];
    const startTime = start.toTimeString().split(' ')[0].substring(0, 5);
    const endTime = end.toTimeString().split(' ')[0].substring(0, 5);

    // Open modal and revert (will save after confirmation)
    setModalInitialData({ date, start_time: startTime, end_time: endTime });
    setIsModalOpen(true);
    resizeInfo.revert();
};
```

#### C. Event Drop (Move)
```javascript
const handleEventDrop = (dropInfo) => {
    const { event } = dropInfo;
    const { start, end } = event;

    // Extract datetime
    const date = start.toISOString().split('T')[0];
    const startTime = start.toTimeString().split(' ')[0].substring(0, 5);
    const endTime = end ? end.toTimeString().split(' ')[0].substring(0, 5) : '';

    // Open modal and revert (will save after confirmation)
    setModalInitialData({ date, start_time: startTime, end_time: endTime });
    setIsModalOpen(true);
    dropInfo.revert();
};
```

### 2. Modal Component (CreateAvailabilityFrameModal.jsx)

The modal displays:
- **Read-only section**: Selected date and time slot
- **Editable fields**: Title, duration, interval, is_recurring, day_of_week, status

```javascript
<CreateAvailabilityFrameModal
    isOpen={isModalOpen}
    onClose={() => setIsModalOpen(false)}
    initialData={{
        date: '2025-12-15',
        start_time: '09:00',
        end_time: '14:00',
        day_of_week: 'Monday',
    }}
    onSuccess={(newFrame) => {
        // Add to calendar
        setAllEvents(prev => [...prev, newFrame]);
    }}
/>
```

### 3. API Call

The modal makes a POST request:

```javascript
const response = await fetch('/api/availability-frames', {
    method: 'POST',
    credentials: 'same-origin',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
    },
    body: JSON.stringify({
        staff_id: user.id,
        date: '2025-12-15',
        start_time: '09:00',
        end_time: '14:00',
        title: 'Morning Consultations',
        duration: 20,
        interval: 10,
        is_recurring: true,
        day_of_week: 'Monday',
        status: 'active',
    }),
});
```

## Backend Implementation

### 1. Route (routes/api.php)

```php
Route::prefix('availability-frames')->middleware('auth')->group(function () {
    Route::post('/', [AvailabilityFrameController::class, 'store']);
    // ... other routes
});
```

### 2. Request Validation (CreateAvailabilityFrameRequest.php)

```php
public function rules(): array
{
    return [
        'staff_id' => ['required', 'integer', 'exists:staff,id'],
        'date' => [Rule::requiredIf(!$this->boolean('is_recurring')), 'nullable', 'date'],
        'start_time' => ['required', 'date_format:H:i:s,H:i'],
        'end_time' => ['required', 'date_format:H:i:s,H:i', 'after:start_time'],
        'title' => ['required', 'string', 'max:255'],
        'duration' => ['required', 'integer', 'min:5'],
        'interval' => ['nullable', 'integer', 'min:0'],
        'is_recurring' => ['nullable', 'boolean'],
        'day_of_week' => [Rule::requiredIf($this->boolean('is_recurring')), 'nullable', 'string'],
        'status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
    ];
}
```

### 3. Controller (AvailabilityFrameController.php)

```php
public function store(CreateAvailabilityFrameRequest $request): JsonResponse
{
    try {
        $frame = AvailabilityFrameFactory::create($request->validated());

        return response()->json([
            'message' => 'Availability frame created successfully',
            'data' => $frame,
        ], 201);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Failed to create availability frame',
            'error' => $e->getMessage(),
        ], 500);
    }
}
```

### 4. Factory (AvailabilityFrameFactory.php)

```php
public static function create(array $data): AvailabilityFrame
{
    try {
        return DB::transaction(function () use ($data) {
            $frame = AvailabilityFrame::create($data);

            Log::info('Availability frame created successfully', [
                'frame_id' => $frame->id,
                'staff_id' => $frame->staff_id,
                'is_recurring' => $frame->is_recurring,
                'repeat_group_id' => $frame->repeat_group_id,
            ]);

            return $frame;
        });
    } catch (\Exception $e) {
        Log::error('Failed to create availability frame', [
            'error' => $e->getMessage(),
            'data' => $data,
        ]);

        throw $e;
    }
}
```

### 5. Model (AvailabilityFrame.php)

The model automatically generates a `repeat_group_id` (UUID) when `is_recurring` is true:

```php
protected static function boot()
{
    parent::boot();

    static::creating(function ($frame) {
        if ($frame->is_recurring && empty($frame->repeat_group_id)) {
            $frame->repeat_group_id = (string) Str::uuid();
        }
    });
}
```

## Complete Example

### User Action: Drag on calendar from 9am to 2pm on Monday, Dec 15, 2025

**1. Calendar captures selection:**
```javascript
{
  date: '2025-12-15',
  start_time: '09:00',
  end_time: '14:00',
  day_of_week: 'Monday'
}
```

**2. Modal shows form with read-only date/time:**
```
Selected Time Slot:
📅 2025-12-15
🕐 09:00 - 14:00

[Editable fields below]
```

**3. User fills in:**
```
Title: Morning Consultations
Duration: 20 minutes
Interval: 10 minutes
☑ Repeat weekly
Day of Week: Monday
Status: Active
```

**4. User clicks "Create Frame"**

**5. POST request:**
```json
{
  "staff_id": 1,
  "date": "2025-12-15",
  "start_time": "09:00",
  "end_time": "14:00",
  "title": "Morning Consultations",
  "duration": 20,
  "interval": 10,
  "is_recurring": true,
  "day_of_week": "Monday",
  "status": "active"
}
```

**6. Backend response (201 Created):**
```json
{
  "message": "Availability frame created successfully",
  "data": {
    "id": 123,
    "staff_id": 1,
    "date": "2025-12-15",
    "title": "Morning Consultations",
    "day_of_week": "Monday",
    "start_time": "09:00:00",
    "end_time": "14:00:00",
    "duration": 20,
    "interval": 10,
    "is_recurring": true,
    "repeat_group_id": "550e8400-e29b-41d4-a716-446655440000",
    "status": "active",
    "created_at": "2025-12-10T10:30:00.000000Z",
    "updated_at": "2025-12-10T10:30:00.000000Z"
  }
}
```

**7. Frontend adds frame to calendar:**
```javascript
setAllEvents(prev => [
    ...prev,
    {
        id: 123,
        title: 'Morning Consultations',
        start: '2025-12-15T09:00:00',
        end: '2025-12-15T14:00:00',
        extendedProps: {
            status: 'active',
            duration: 20,
            interval: 10,
            is_recurring: true,
        },
    },
]);
```

## Testing

### Manual Testing Steps

1. Login as staff member
2. Navigate to calendar
3. Switch to week or day view
4. Drag to select a time range (e.g., 9am to 2pm)
5. Modal should appear with pre-filled date/time
6. Fill in title, duration, interval
7. Click "Create Frame"
8. Verify frame appears on calendar
9. Test resize: drag edge of existing event
10. Test move: drag event to new time

### API Testing with curl

```bash
curl -X POST http://localhost:8000/api/availability-frames \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "X-CSRF-TOKEN: your-csrf-token" \
  --cookie "laravel_session=your-session-cookie" \
  -d '{
    "staff_id": 1,
    "date": "2025-12-15",
    "start_time": "09:00",
    "end_time": "14:00",
    "title": "Morning Consultations",
    "duration": 20,
    "interval": 10,
    "is_recurring": true,
    "day_of_week": "Monday",
    "status": "active"
  }'
```

## Files Modified/Created

### Backend
- `@docs/api/availability-frame-create.md` - API contract
- `app/Http/Requests/CreateAvailabilityFrameRequest.php` - Validation
- `app/Factories/AvailabilityFrameFactory.php` - Business logic
- `app/Http/Controllers/AvailabilityFrameController.php` - Updated store method

### Frontend
- `resources/js/components/CreateAvailabilityFrameModal.jsx` - Modal component
- `resources/js/components/AppointmentCalendar.jsx` - Calendar integration
- `resources/css/app.css` - Modal and form styles

### Documentation
- `@docs/features/calendar-availability-frame-integration.md` - This file
