# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 12 PHP backend application for the NYK-FIL Crew Portal. It manages maritime crew information, vessel assignments, contracts, and administrative workflows. The application uses Laravel Sanctum for API authentication with a custom OTP-based login system.

## Development Commands

### Starting Development Environment

```bash
composer dev
```

This starts all development services concurrently:

-   PHP development server (`php artisan serve`)
-   Queue worker (`php artisan queue:listen --tries=1`)
-   Log monitoring (`php artisan pail --timeout=0`)
-   Vite development server (`npm run dev`)

### Individual Commands

```bash
# Start development server only
php artisan serve

# Run database migrations
php artisan migrate

# Refresh migrations with seeding
php artisan migrate:refresh --seed

# Clear application caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

### Testing

```bash
# Run all tests
composer test
# or
php artisan test

# Run specific test suites
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature

# Run tests with coverage
php artisan test --coverage
```

### Code Quality

```bash
# Format code with Laravel Pint
./vendor/bin/pint

# Run in check mode (no changes)
./vendor/bin/pint --test
```

## Architecture

### Authentication System

-   **OTP-Based Login**: Users authenticate via email with a time-limited OTP sent to their inbox
-   **Flow**: `POST /api/auth/login` → Receive OTP via email → `POST /api/auth/verify` with OTP and session token → Receive Sanctum token
-   **Rate Limiting**: 3 attempts per minute per IP, max 5 OTP verification attempts
-   **Session Token**: Each OTP request generates a unique session token that must be provided during verification
-   **OTP Storage**: `OtpVerification` model stores hashed OTPs with 10-minute expiry
-   **Controller**: `App\Http\Controllers\Api\AuthController`

### Role-Based Access Control

The application has two user types distinguished by the `is_crew` flag in the `users` table:

-   **Crew Members** (`is_crew = 1`): Access crew-specific routes under `/api/crew/*` via `crew` middleware
-   **Admin Users** (`is_crew = 0`): Access admin routes under `/api/admin/*` via `admin` middleware
-   **Middleware**: `app/Http/Middleware/EnsureCrew.php` and `EnsureAdmin.php`

### API Structure

-   **Public Routes**: `/api/auth/*` (login, verify OTP, resend OTP)
-   **Protected Routes**: Require `auth:sanctum` middleware
-   **Crew Routes**: `/api/crew/*` - Limited read access to own data
-   **Admin Routes**: `/api/admin/*` - Full CRUD access to all resources
-   **Controllers**: Located in `app/Http/Controllers/Api/`

### Database Architecture

The database follows a normalized hierarchical structure. See `database/DATABASE_STRUCTURE.md` for complete schema documentation.

**Key Entity Hierarchies:**

1. **Geographic Data**: Islands → Regions → Provinces → Cities
2. **Rank System**: RankCategories → RankGroups → Ranks
3. **Maritime Structure**: VesselTypes → Vessels (linked to Fleets)
4. **Crew Management**: Users (crew), Addresses, Allotees, Contracts
5. **Document Management**: EmploymentDocumentType/TravelDocumentType → EmploymentDocument

**Database Configuration:**

-   Development: SQLite (`database/database.sqlite`)
-   Production: MySQL/MariaDB support configured
-   All major models use soft deletes (`deleted_at`)
-   Audit trail: `modified_by` field populated via `HasModifiedBy` trait

### Model Patterns

**Common Traits:**

-   `SoftDeletes`: Enables soft deletion on models (use this for all major entities)
-   `HasModifiedBy`: Automatically sets `modified_by` field with authenticated user's full name on create/update

**Creating New Models:**

When creating models that track user modifications, always:

1. Add `modified_by` column in migration: `$table->string('modified_by')->nullable();`
2. Add `softDeletes()` in migration: `$table->softDeletes();`
3. Use traits in model: `use SoftDeletes, HasModifiedBy;`
4. Import trait: `use App\Traits\HasModifiedBy;`

**Naming Conventions:**

-   Foreign keys: Use standard Laravel conventions (`user_id`, `vessel_id`)
-   Crew-specific foreign keys: Use `crew_id` when referring to users in their crew role
-   Pivot tables: Prefix with main entity name (e.g., `crew_allotees` not `user_allotees`)

### Testing

-   PHPUnit configuration in `phpunit.xml`
-   Feature tests in `tests/Feature/`
-   Unit tests in `tests/Unit/`
-   Uses in-memory SQLite for testing
-   Authentication tests included for OTP-based login flow

### Key Configuration

-   Application uses SQLite by default (development)
-   Queue connection set to database
-   Cache store set to database
-   Mail driver set to log for development
-   Environment variables in `.env.example`

### Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/              # API resource controllers
│   │   └── Auth/             # Breeze auth controllers (legacy)
│   └── Middleware/           # Custom middleware (EnsureCrew, EnsureAdmin)
├── Models/                   # Eloquent models
├── Traits/                   # Reusable traits (HasModifiedBy)
└── Providers/               # Service providers

database/
├── factories/               # Model factories
├── migrations/             # Database migrations (ordered by timestamp)
├── seeders/                # Database seeders
└── DATABASE_STRUCTURE.md   # Complete schema documentation

routes/
├── api.php                 # Main API routes (auth, crew, admin)
├── auth.php                # Breeze auth routes (legacy)
├── console.php             # Console commands
└── web.php                 # Web routes

tests/
├── Feature/                # Feature tests
└── Unit/                   # Unit tests
```
