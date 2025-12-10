# Create Availability Frame API

## Endpoint
```
POST /api/availability-frames
```

## Authentication
Required: Yes (Staff must be authenticated)

## Request Headers
```
Content-Type: application/json
Accept: application/json
```

## Request Body

```json
{
  "staff_id": 1,
  "date": "2025-12-15",
  "start_time": "09:00:00",
  "end_time": "14:00:00",
  "title": "Morning Availability",
  "duration": 20,
  "interval": 10,
  "is_recurring": true,
  "day_of_week": "Monday",
  "status": "active"
}
```

## Request Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `staff_id` | integer | Yes | ID of the staff member |
| `date` | string (YYYY-MM-DD) | Yes* | Specific date for the frame (*required if not recurring) |
| `start_time` | string (HH:mm:ss) | Yes | Start time of availability |
| `end_time` | string (HH:mm:ss) | Yes | End time of availability |
| `title` | string | Yes | Title/description of the availability frame |
| `duration` | integer | Yes | Duration of each slot in minutes (e.g., 20) |
| `interval` | integer | No | Gap between slots in minutes (default: 0) |
| `is_recurring` | boolean | No | Whether this frame repeats weekly (default: false) |
| `day_of_week` | string | Yes* | Day of week (*required if recurring). Values: Monday, Tuesday, Wednesday, Thursday, Friday, Saturday, Sunday |
| `status` | string | No | Status of the frame (default: "active"). Values: active, inactive |

## Validation Rules

- `staff_id`: must exist in staff table
- `date`: required if `is_recurring` is false
- `start_time`: must be valid time format (HH:mm:ss or HH:mm)
- `end_time`: must be after `start_time`
- `title`: max 255 characters
- `duration`: must be positive integer, min 5 minutes
- `interval`: must be non-negative integer
- `is_recurring`: boolean
- `day_of_week`: required if `is_recurring` is true
- `status`: must be "active" or "inactive"

## Success Response

**Status Code:** `201 Created`

```json
{
  "message": "Availability frame created successfully",
  "data": {
    "id": 123,
    "staff_id": 1,
    "date": "2025-12-15",
    "title": "Morning Availability",
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

## Error Responses

### Validation Error
**Status Code:** `422 Unprocessable Entity`

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "end_time": [
      "The end time must be after start time."
    ],
    "duration": [
      "The duration must be at least 5."
    ]
  }
}
```

### Unauthorized
**Status Code:** `401 Unauthorized`

```json
{
  "message": "Unauthenticated."
}
```

### Server Error
**Status Code:** `500 Internal Server Error`

```json
{
  "message": "Failed to create availability frame",
  "error": "Error details"
}
```

## Example Usage

### Non-Recurring Frame (Single Date)
```javascript
const response = await fetch('/api/availability-frames', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
  },
  credentials: 'same-origin',
  body: JSON.stringify({
    staff_id: 1,
    date: '2025-12-15',
    start_time: '09:00',
    end_time: '14:00',
    title: 'Available for consultations',
    duration: 30,
    interval: 10,
    is_recurring: false,
    status: 'active'
  })
});

const data = await response.json();
```

### Recurring Frame (Weekly)
```javascript
const response = await fetch('/api/availability-frames', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
  },
  credentials: 'same-origin',
  body: JSON.stringify({
    staff_id: 1,
    date: '2025-12-15', // First occurrence date
    start_time: '09:00',
    end_time: '14:00',
    title: 'Monday Morning Sessions',
    duration: 20,
    interval: 10,
    is_recurring: true,
    day_of_week: 'Monday',
    status: 'active'
  })
});

const data = await response.json();
```

## Notes

- When `is_recurring` is true, a unique `repeat_group_id` (UUID) is automatically generated
- The `repeat_group_id` allows bulk operations on all frames in the same recurring group
- Time fields accept both "HH:mm" and "HH:mm:ss" formats
- The authenticated staff member's ID will be used if `staff_id` is not provided (or can be enforced to match authenticated user)
- Slots will be automatically generated based on duration and interval settings
