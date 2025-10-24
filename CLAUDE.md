# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

```bash
# Setup and start all services (server, queue, logs, vite)
composer run dev

# Run tests
composer test
php artisan test tests/Feature/AppointmentControllerTest.php

# Format only edited files (NEVER entire project)
vendor/bin/pint path/to/file.php
```

## Architecture Principles

### Repository Pattern
- All database queries go through Repositories (`app/Repositories/`)
- Repositories return raw models/collections (no transformation)
- Controllers inject repositories via constructor and delegate all queries
- Use eager loading to prevent N+1: `->with(['relation'])`

### Thin Controllers
- Validate via Form Requests (`app/Http/Requests/`)
- Delegate queries to Repositories
- Return JSON responses
- No business logic in controllers

### Testing with Mocks
- Feature tests mock repositories using Mockery
- Mock in `setUp()`: `$this->app->instance(AppointmentRepository::class, $mockRepo)`
- Test controller behavior, not database operations

Example:
```php
$this->mockRepo->shouldReceive('create')->once()->andReturn($mock);
$response = $this->postJson('/api/appointments', $data);
```

## Key Rules

- **No raw SQL** - always use ORM methods through Repositories
- **No speculative code** - only create functions when needed
- **Fail-fast** - no silent fallbacks or suppressed errors
- **Trace before coding** - examine source/database before implementation
- **Test before delivery** - always verify code works

## Tech Stack

Laravel 12 + React 19 + Vite 7 + Tailwind CSS 4 + MySQL
