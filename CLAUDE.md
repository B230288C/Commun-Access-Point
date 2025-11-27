# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

```bash
# Setup and start all services (server, queue, logs, vite)
composer run dev

# Run tests
composer test
php artisan test tests/Feature/AppointmentControllerTest.php

# Run specific test file
php artisan test tests/Feature/[TestName].php

# Format only edited files (NEVER entire project)
vendor/bin/pint path/to/file.php

# Clear config cache (needed before running tests)
php artisan config:clear
```

## Architecture Overview

This is a Laravel 12 + React 19 appointment management system for staff and visitor access control. The system manages staff availability and visitor appointments through a calendar interface.

### Feature Scope

#### Staff Capabilities
- Login and authentication into Access Point
- Set availability through weekly recurring frames (e.g., 9am-2pm, 20 min slots + 10 min gaps, Mon-Fri excluding Wed)
- Update availability through recurring frames with date range and status
- Approve visitor appointment applications
- Block specific availability slots on calendar as appointments
- Cancel appointments
- Notify visitors through notification channels (Email, Email + Slack, Email + Telegram)
- View and manage appointments and availability in a calendar

#### Visitor Capabilities
- Register appointments through staff appointment links
- Link associates one or many availability frames for a staff member

### Core Entities

- **Appointments**: Visitor appointments scheduled with staff
- **Availability Frames**: Recurring time patterns with start/end dates and status
- **Availability Slots**: Individual time slots generated from frames
- **Notification Channels**: Email (bonus: Slack, Telegram)
- **Calendar**: Staff calendar containing multiple availability frames

### Directory Structure

- `app/Models/` - Eloquent models (Appointment, AvailabilityFrame, AvailabilitySlot, Staff, User, NotificationChannel)
- `app/Repositories/` - Data access layer for all database queries
- `app/Http/Controllers/` - API/web controllers (thin, delegate to repositories/factories)
- `app/Http/Requests/` - Form request validation classes
- `app/Http/Resources/` - Data transformation for API responses
- `app/Factories/` - Business logic for create/update/delete operations
- `app/Enums/` - Status enums (AppointmentStatus, AvailabilityFrameStatus, AvailabilitySlotStatus)
- `app/Jobs/` - Asynchronous jobs for notifications
- `routes/api.php` - REST API endpoints
- `routes/web.php` - Web routes (authentication, views)
- `tests/Feature/` - Feature tests using mocked repositories
- `@docs/` - Project documentation
  - `plan/` - Planning and progress tracking
  - `architecture/` - Code architecture navigation
  - `features/` - Entities relations and code file mapping
  - `components/` - Component gallery (React components)

## Architecture Principles

### Repository Pattern
- All database queries go through Repositories (`app/Repositories/`)
- Repositories return raw models/collections with eager loading to prevent N+1 queries
- Example: `Appointment::with('staff')->findOrFail($id)`
- Do NOT transform or modify data in repositories; return raw models

### Factory Pattern for Business Logic
- Use Factories (`app/Factories/`) for create/update/delete operations
- Factories wrap operations in DB::transaction() for data consistency
- Factories use logging to capture errors: `Log::error('Operation failed', [...])`
- Controllers delegate to factories rather than directly modifying models
- Example: `AppointmentFactory::create($validated)` or `AppointmentFactory::update($appointment, $validated)`

### Thin Controllers
- Controllers validate input via Form Requests (app/Http/Requests/)
- Controllers inject repositories and factories via constructor
- Controllers delegate queries to repositories and mutations to factories
- Return JSON resources for API routes, views for web routes
- No business logic or data queries in controller methods

### API Resources
- Use Resources (`app/Http/Resources/`) to transform models for API responses
- Resources provide consistent data format for API consumers
- Never return raw models from API endpoints

### Asynchronous Processing
- Use Jobs (`app/Jobs/`) for time-consuming operations like sending notifications
- Dispatch jobs from factories using Laravel's queue system
- Example: `SendNotificationJob::dispatch($appointment)` within DB::transaction()

### API Routes
- All routes defined in `routes/api.php` under RESTful conventions
- Use route prefixes to group related endpoints (e.g., `/appointments`, `/availability-frames`)
- Route-model binding for single resource retrieval

### Testing with Mocks
- Feature tests use Mockery to mock repositories
- Mock repositories in `setUp()` method: `$this->app->instance(RepositoryClass::class, $mockRepo)`
- Test controller behavior and API responses, not database operations
- Close Mockery in `tearDown()` to prevent memory leaks

Example test structure:
```php
public function setUp(): void {
    parent::setUp();
    $this->mockRepo = Mockery::mock(AppointmentRepository::class);
    $this->app->instance(AppointmentRepository::class, $this->mockRepo);
}

public function can_create_appointment() {
    $this->mockRepo->shouldReceive('create')->once()->andReturn($mock);
    $response = $this->postJson('/api/appointments', $data);
    $response->assertStatus(201);
}
```

## Coding Standards

### PSR-12 & Laravel Conventions
- Follow PSR-12 coding standards for all PHP files
- Use Laravel naming conventions for classes, methods, and files
- Use camelCase for method names, PascalCase for class names, snake_case for database columns

### Code Organization
- **Repositories** - All database queries and reads
- **Factories** - All create/update/delete operations and business logic
- **Requests** - Input validation and authorization rules
- **Resources** - API response data transformation
- **Jobs** - Asynchronous processing (notifications, emails)
- **Models** - Data representation with relationships
- **Enums** - Status and constant definitions

### Key Rules

- **No raw SQL** - always use ORM methods through Repositories
- **No speculative code** - only create functions when actively needed
- **Fail-fast** - throw exceptions rather than silent fallbacks
- **Trace before coding** - examine existing code patterns and database before implementation
- **Test before delivery** - run tests to verify functionality works
- **Always eager load** - use `->with(['relation'])` in repositories to prevent N+1 queries
- **Use Enums for status fields** - refer to status enums rather than hardcoded strings
- **Use Transactions** - wrap create/update/delete operations in `DB::transaction()` for atomicity
- **Document complex logic** - use comments and docblocks on non-obvious functions

## Documentation

### Documentation Structure
Project documentation is organized in the `@docs/` directory:

- **`@docs/plan/`** - Planning and progress tracking
  - Feature planning documents
  - Implementation progress tracking
  - Roadmap and milestones

- **`@docs/architecture/`** - Code architecture navigation
  - System architecture overview
  - Data flow diagrams
  - Component interactions

- **`@docs/features/`** - Entities relations and code file mapping
  - Entity relationship diagrams
  - Feature implementation guide
  - File structure for each feature

- **`@docs/components/`** - Component gallery
  - React component documentation
  - Component usage examples
  - UI component specifications

### Documentation Standards
- Use Markdown (.md) for all documentation
- Include docblocks on complex functions and methods
- Document non-obvious business logic with clear comments
- Maintain README files for project overview and entry points
- Track test coverage for critical features

## Tech Stack

- **Backend**: Laravel 12, PHP 8.2+
- **Frontend**: React 19, Vite 7, Tailwind CSS 4
- **Database**: MySQL
- **Testing**: PHPUnit 11, Mockery
- **Code Quality**: Pint (Laravel code formatter)

## UI Guidelines

- The staff/visitor calendar uses React Big Calendar.
- All styling for calendar events, slots, headers, and tooltips must follow `style_guide.md`.
- When generating or editing calendar components, map frame/slot data to Big Calendar events accordingly.
- Claude is allowed to modify `style_guide.md` when necessary.
- Any change to `style_guide.md` must be applied consistently across all affected components.
- All colors, typography, spacing, and states in existing components must be updated to match the revised style guide.
- Always check for and correct inconsistencies in UI components whenever `style_guide.md` is changed.

Claude must follow `style_guide.md` as the single source of truth for all UI elements, including:
- Colors (primary, neutral, hover, active, disabled, muted)
- Typography (fonts, sizes, line heights)
- Spacing (padding, margin, gaps)
- Border radius, card, buttons, inputs, tables
- Icon sizes and states

Rules for modifying or creating components:
1. When generating new UI components (Blade, Tailwind, CSS, React/JSX), always refer to `style_guide.md` for colors, fonts, spacing, and states.
2. When editing existing components, check for inconsistencies with `style_guide.md` and correct them automatically.
3. If a design detail is missing, infer it from the closest matching pattern in `style_guide.md`.
4. Do not introduce new styles that are not defined or consistent with `style_guide.md`.

### Style Guide Modification Workflow
1. If a change to `style_guide.md` is needed, first identify which components are affected.
2. Update `style_guide.md` with the new style or correction.
3. Immediately update all affected components to comply with the new guide.
4. Run UI tests or visual checks to ensure consistency.
5. Document the change in `@docs/components/` if necessary.
