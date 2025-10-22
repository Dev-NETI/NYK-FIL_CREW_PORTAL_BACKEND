# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

NYK-FIL Crew Portal Backend - Laravel 12 REST API for maritime crew management system. Handles authentication, crew profiles, document management, vessel assignments, and administrative workflows.

**Tech Stack:**

-   Laravel 12 (PHP 8.2)
-   Laravel Sanctum (token-based authentication)
-   SQLite (development) / MySQL/MariaDB (production)
-   XAMPP environment (not containerized)

## Commands

### Development Workflow

```bash
# Start all development services (server, queue worker, logs, vite)
composer dev

# Individual services
php artisan serve                    # Development server (http://localhost:8000)
php artisan queue:listen --tries=1   # Queue worker
php artisan pail --timeout=0         # Real-time log viewer
npm run dev                          # Vite asset compilation
```

### Database Operations

```bash
# Run migrations
php artisan migrate

# Fresh database with seed data
php artisan migrate:refresh --seed

# Run specific seeder
php artisan db:seed --class=JobDescriptionRequestSeeder

# Generate new migration
php artisan make:migration create_table_name
```

### Code Generation

```bash
# Model with migration
php artisan make:model ModelName -m

# Controller
php artisan make:controller Api/ControllerName

# Seeder
php artisan make:seeder TableNameSeeder
```

### Testing & Quality

```bash
# Run all tests
composer test
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Code formatting (Laravel Pint)
./vendor/bin/pint

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

## Architecture

### Authentication Flow (OTP-based)

The system uses a passwordless OTP authentication via Laravel Sanctum:

1. **POST `/api/auth/login`** - User submits email

    - Generates 6-digit OTP
    - Creates `OtpVerification` record with hashed OTP
    - Returns session token + OTP (logged to console in dev)
    - Rate limited: 3 attempts per minute per IP

2. **POST `/api/auth/verify`** - User submits OTP + session token

    - Validates OTP against hashed version
    - Tracks failed attempts (max 5)
    - Issues Sanctum token on success
    - Updates `email_verified_at` on first login
    - Records `last_login_at` and `last_login_ip`

3. **Subsequent requests** - Include `Authorization: Bearer {token}` header

**Key Files:**

-   [app/Http/Controllers/Api/AuthController.php](app/Http/Controllers/Api/AuthController.php)
-   [app/Models/OtpVerification.php](app/Models/OtpVerification.php)
-   [app/Models/User.php](app/Models/User.php)

### Role-Based Access Control

Two user types distinguished by `users.is_crew` boolean:

**Crew Users (`is_crew = 1`):**

-   Middleware: `auth:sanctum` + `crew` middleware
-   Routes: `/api/crew/*`
-   Access: Own profile, documents, job description requests
-   Profile relationships: `UserProfile`, `UserContact`, `UserEducation`, `UserEmployment`, `UserPhysicalTrait`

**Admin Users (`is_crew = 0`):**

-   Middleware: `auth:sanctum` + `admin` middleware
-   Routes: `/api/admin/*`
-   Access: Full CRUD on all resources
-   Profile relationship: `AdminProfile`
-   Role system: `AdminRole` (many-to-many with `Role`)

**Implementation:**

-   [app/Http/Middleware/EnsureCrew.php](app/Http/Middleware/EnsureCrew.php)
-   [app/Http/Middleware/EnsureAdmin.php](app/Http/Middleware/EnsureAdmin.php)
-   [routes/api.php](routes/api.php)

### Model Patterns

**Standard Model Structure:**

```php
namespace App\Models;

use App\Traits\HasModifiedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Example extends Model
{
    use SoftDeletes, HasModifiedBy;

    protected $fillable = [
        'field1',
        'field2',
        'crew_id',
    ];

    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function crew(): BelongsTo
    {
        return $this->belongsTo(User::class, 'crew_id');
    }
}
```

**Key Traits:**

-   `SoftDeletes` - Adds `deleted_at` timestamp, never hard deletes
-   `HasModifiedBy` - Auto-populates `modified_by` with authenticated admin's full name on create/update
-   `HasApiTokens` - Used on `User` model for Sanctum

### API Response Patterns

**Success Responses:**

```php
// GET requests
return response()->json($data, 200);

// POST requests (resource created)
return response()->json($resource, 201);

// PUT/PATCH requests
return response()->json($updatedResource, 200);

// DELETE requests
return response()->json(['message' => 'Deleted successfully'], 200);
```

**Error Responses:**

```php
// Validation errors
return response()->json([
    'success' => false,
    'message' => 'Validation failed',
    'errors' => $validator->errors()
], 422);

// Not found
return response()->json([
    'success' => false,
    'message' => 'Resource not found'
], 404);

// Server errors
return response()->json([
    'success' => false,
    'message' => 'Server error',
    'error' => $e->getMessage()
], 500);
```

### Database Schema Conventions

**Naming:**

-   Tables: Plural snake_case (`employment_documents`, `job_description_requests`)
-   Foreign keys: `{table_singular}_id` (e.g., `crew_id`, `vessel_id`)
-   Timestamps: `created_at`, `updated_at`, `deleted_at`
-   Audit field: `modified_by` (string, not FK - stores admin full name)

**Common Columns:**

```sql
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
-- or for custom IDs:
id VARCHAR(50) PRIMARY KEY  -- e.g., "JD-2025-001"

created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
deleted_at TIMESTAMP NULL  -- SoftDeletes
modified_by VARCHAR(255) NULL  -- HasModifiedBy trait
```

**Entity Hierarchies:**

1. **Geographic**: `islands` → `regions` → `provinces` → `cities` → `barangays`
2. **Maritime**: `vessel_types` → `vessels` → `fleets`
3. **Ranks**: `rank_categories` → `rank_groups` → `ranks`
4. **Documents**: `employment_document_types` → `employment_documents`
5. **Crew**: `users` (crew) → `user_profiles`, `addresses`, `contracts`, `allotees`

## Key Features & Modules

### Document Management System

**Employment Documents:**

-   Model: [app/Models/EmploymentDocument.php](app/Models/EmploymentDocument.php)
-   Controller: [app/Http/Controllers/Api/EmploymentDocumentController.php](app/Http/Controllers/Api/EmploymentDocumentController.php)
-   Types: Defined in `employment_document_types` table
-   File storage: `storage/app/employment_documents/{crew_id}/`
-   Update approval workflow via `EmploymentDocumentUpdate` model

**Travel Documents:**

-   Model: [app/Models/TravelDocument.php](app/Models/TravelDocument.php)
-   Controller: [app/Http/Controllers/Api/TravelDocumentController.php](app/Http/Controllers/Api/TravelDocumentController.php)
-   Types: Passport, visa, seaman's book, etc.
-   Expiry tracking: `issue_date`, `expiry_date` columns
-   File storage: `storage/app/travel_documents/{crew_id}/`

**Document Approval Workflow:**

-   Crew submits document updates
-   Creates `EmploymentDocumentUpdate` record with status `pending`
-   Admin reviews via [EmploymentDocumentApprovalController](app/Http/Controllers/Api/EmploymentDocumentApprovalController.php)
-   Approval: Applies changes to original document, status → `approved`
-   Rejection: Document unchanged, status → `rejected`, stores `rejection_reason`

### Job Description Request Module

Complete workflow for crew requesting official job description documents. See [JOB_DESCRIPTION_MODULE.md](JOB_DESCRIPTION_MODULE.md) for full documentation.

**Key Points:**

-   Model: [app/Models/JobDescriptionRequest.php](app/Models/JobDescriptionRequest.php)
-   Custom ID format: `JD-YYYY-###` (auto-generated, year-based sequence)
-   Memo number format: `NYK-JD-YYYY-###` (generated when EA processes)
-   Purpose types: SSS, PAG_IBIG, PHILHEALTH, VISA (with subtypes)
-   Workflow states: `pending` → `in_progress` → `ready_for_approval` → `approved`/`disapproved`

**Status Transitions:**

```php
// Check if request can transition
$request->canBeProcessed();      // pending → in_progress
$request->canBeApproved();        // ready_for_approval → approved
$request->canBeDownloaded();      // approved + signature_added
```

**Query Scopes:**

```php
JobDescriptionRequest::pending()->get();
JobDescriptionRequest::forCrew($crewId)->get();
JobDescriptionRequest::readyForApproval()->get();
```

### User Profile System

Crew members have multiple related profile tables:

-   `user_profiles` - Basic info (name, birthdate, citizenship, etc.)
-   `user_contacts` - Contact details (phone, emergency contact)
-   `user_education` - Educational background
-   `user_employment` - Current employment info (rank, vessel, etc.)
-   `user_physical_traits` - Physical characteristics
-   `addresses` - Residential addresses

All linked via `crew_id` (which references `users.id` where `is_crew = 1`).

### Geography API

Hierarchical Philippine location data:

```php
// Get all regions
GET /api/geography/regions

// Get provinces by region code
GET /api/geography/provinces?regCode=01

// Get cities by province code
GET /api/geography/cities?provCode=0128

// Get barangays by city code
GET /api/geography/barangays?cityCode=012801
```

Controller: [app/Http/Controllers/Api/GeographyController.php](app/Http/Controllers/Api/GeographyController.php)

## Development Patterns

### Creating a New Model

```bash
# Generate model with migration
php artisan make:model ModelName -m
```

**Add to model:**

```php
use SoftDeletes, HasModifiedBy;

protected $fillable = ['field1', 'field2'];

protected function casts(): array {
    return ['deleted_at' => 'datetime'];
}

// Define relationships
public function crew(): BelongsTo {
    return $this->belongsTo(User::class, 'crew_id');
}
```

**In migration:**

```php
Schema::create('table_name', function (Blueprint $table) {
    $table->id();
    $table->foreignId('crew_id')->constrained('users')->onDelete('cascade');
    $table->string('field1');
    $table->text('field2')->nullable();
    $table->string('modified_by')->nullable();
    $table->timestamps();
    $table->softDeletes();

    $table->index('crew_id');
});
```

### Adding API Endpoints

1. **Create controller method:**

```php
// app/Http/Controllers/Api/ExampleController.php
public function index()
{
    $data = Example::with('crew')->get();
    return response()->json($data);
}
```

2. **Register route in [routes/api.php](routes/api.php):**

```php
// Admin-only routes
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::apiResource('examples', ExampleController::class);
});

// Crew-only routes
Route::middleware(['auth:sanctum', 'crew'])->prefix('crew')->group(function () {
    Route::get('examples', [ExampleController::class, 'index']);
});
```

### File Upload Pattern

```php
public function store(Request $request)
{
    $validated = $request->validate([
        'file' => 'required|file|mimes:pdf,jpg,png|max:5120', // 5MB
        'crew_id' => 'required|exists:users,id',
    ]);

    $file = $request->file('file');
    $path = $file->store("documents/{$validated['crew_id']}", 'local');

    $document = Document::create([
        'crew_id' => $validated['crew_id'],
        'file_path' => $path,
        'file_ext' => $file->getClientOriginalExtension(),
    ]);

    return response()->json($document, 201);
}
```

### Eager Loading to Avoid N+1

Always use `with()` when loading related models:

```php
// Bad - N+1 query problem
$documents = EmploymentDocument::all();
foreach ($documents as $doc) {
    echo $doc->crew->name; // Triggers query for each iteration
}

// Good - Single query with joins
$documents = EmploymentDocument::with('crew', 'employmentDocumentType')->get();
foreach ($documents as $doc) {
    echo $doc->crew->name; // No additional query
}
```

### Query Scopes for Reusability

```php
// In model
public function scopeActive($query)
{
    return $query->whereNull('deleted_at');
}

public function scopeForCrew($query, $crewId)
{
    return $query->where('crew_id', $crewId);
}

// Usage
$activeDocuments = EmploymentDocument::active()->forCrew($crewId)->get();
```

## Testing

### Feature Test Structure

```php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_crew_can_access_own_data()
    {
        $crew = User::factory()->create(['is_crew' => 1]);

        $response = $this->actingAs($crew, 'sanctum')
            ->getJson('/api/crew/dashboard');

        $response->assertStatus(200);
    }
}
```

### Run Tests

```bash
# All tests
composer test

# Specific test file
php artisan test --filter ExampleTest

# With coverage
php artisan test --coverage
```

## Important Notes

### Foreign Key Conventions

-   `user_id` - References any user (crew or admin)
-   `crew_id` - References crew user specifically (`users.id` where `is_crew = 1`)
-   `modified_by` - NOT a foreign key, stores admin full name as string

### CORS Configuration

Backend is configured to accept requests from `http://localhost:3000` (frontend). Update `config/cors.php` if frontend URL changes.

### File Storage

-   Development: `storage/app/` (local filesystem)
-   Production: Configure S3 or similar in `config/filesystems.php`
-   Public files: Use `storage/app/public` and create symlink: `php artisan storage:link`

### Queue System

OTP emails and notifications should be queued:

```php
Mail::to($user)->queue(new OtpMail($otp));
```

Run queue worker: `php artisan queue:listen`

### Rate Limiting

Authentication endpoints use rate limiting:

-   3 attempts per minute per IP for login initiation
-   5 OTP verification attempts per session

### Environment Variables

Key `.env` variables:

```env
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:3000

DB_CONNECTION=sqlite  # or mysql for production

SANCTUM_STATEFUL_DOMAINS=localhost:3000
SESSION_DOMAIN=localhost
```

## Common Issues

### Migration Errors

```bash
# If foreign key constraint fails
php artisan migrate:fresh --seed

# Check migration status
php artisan migrate:status
```

### Sanctum Token Issues

```bash
# Clear config cache
php artisan config:clear

# Ensure frontend sends CSRF cookie request first
GET /sanctum/csrf-cookie
```

### File Permission Errors (XAMPP Windows)

Ensure `storage/` and `bootstrap/cache/` are writable:

```bash
# In Git Bash or WSL
chmod -R 775 storage bootstrap/cache
```

## Related Documentation

-   Parent monorepo: [../CLAUDE.md](../CLAUDE.md)
-   Job Description Module: [JOB_DESCRIPTION_MODULE.md](JOB_DESCRIPTION_MODULE.md)
-   Frontend: [../NYK-FIL_CREW_PORTAL_FRONTEND/CLAUDE.md](../NYK-FIL_CREW_PORTAL_FRONTEND/CLAUDE.md)
-   Laravel 12 Docs: https://laravel.com/docs/12.x
-   Sanctum Docs: https://laravel.com/docs/12.x/sanctum
