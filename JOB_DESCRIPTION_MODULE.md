# Job Description Module - Backend Implementation

This document provides comprehensive documentation for the Job Description module's backend implementation in the NYK-FIL Crew Portal Laravel application.

## 📁 Module Structure

```
CREW_PORTAL_BACKEND/
├── app/Models/
│   └── JobDescriptionRequest.php           # Main model for job description requests
├── database/migrations/
│   └── 2025_08_28_055903_create_job_description_requests_table.php
├── database/seeders/
│   └── JobDescriptionRequestSeeder.php     # Sample data for development
└── JOB_DESCRIPTION_MODULE.md              # This documentation
```

## 🎯 Module Overview

The Job Description module handles the complete workflow for crew members requesting official job description documents for various purposes (SSS, Pag-Ibig, PhilHealth, VISA applications). The module supports a three-stage approval process:

1. **Crew Submission** - Crew members submit requests
2. **EA Processing** - Executive Assistants process and generate documents  
3. **VP Approval** - Vice Presidents approve and digitally sign documents

## 📊 Database Schema

### JobDescriptionRequest Model

The main entity that handles all job description requests and their complete lifecycle.

**Table: `job_description_requests`**

```sql
CREATE TABLE job_description_requests (
    id VARCHAR(50) PRIMARY KEY,                    -- Auto-generated: "JD-YYYY-###"
    crew_id BIGINT UNSIGNED NOT NULL,              -- Links to users table
    purpose ENUM('SSS', 'PAG_IBIG', 'PHILHEALTH', 'VISA') NOT NULL,
    visa_type ENUM('TOURIST', 'BUSINESS', 'WORK', 'TRANSIT', 'STUDENT', 'FAMILY', 'SEAMAN') NULL,
    notes TEXT NULL,                               -- Optional crew notes
    status ENUM('pending', 'in_progress', 'ready_for_approval', 'approved', 'disapproved') DEFAULT 'pending',
    
    -- Document generation data (filled by EA)
    memo_no VARCHAR(100) NULL,                     -- Auto-generated: "NYK-JD-YYYY-###"
    
    -- Processing information
    processed_by BIGINT UNSIGNED NULL,             -- EA user ID
    processed_date TIMESTAMP NULL,
    
    -- VP approval information
    approved_by BIGINT UNSIGNED NULL,              -- VP user ID
    approved_date TIMESTAMP NULL,
    disapproval_reason TEXT NULL,
    vp_comments TEXT NULL,
    signature_added BOOLEAN DEFAULT FALSE,
    
    -- Audit fields (HasModifiedBy trait)
    modified_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,                     -- SoftDeletes support
    
    -- Foreign key constraints
    FOREIGN KEY (crew_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (modified_by) REFERENCES users(id) ON DELETE SET NULL,
    
    -- Indexes for performance
    INDEX idx_crew_id (crew_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_processed_by (processed_by)
);
```

## 🏗️ Model Implementation

### JobDescriptionRequest Model

**File: `app/Models/JobDescriptionRequest.php`**

#### Key Features

- **Custom Primary Key**: Uses string-based IDs with format `JD-YYYY-###`
- **Auto-increment Logic**: Automatically generates sequential IDs per year
- **HasModifiedBy Trait**: Tracks who modified the record
- **SoftDeletes**: Supports soft deletion for audit purposes
- **Rich Relationships**: Connects to User model for crew, EA, and VP references

#### Fillable Attributes

```php
protected $fillable = [
    'crew_id',
    'purpose', 
    'visa_type',
    'notes',
    'status',
    'memo_no',
    'hire_date',
    'rank',
    'vessel_type', 
    'contract_start_date',
    'contract_end_date',
    'processed_by',
    'processed_date',
    'approved_by',
    'approved_date',
    'disapproval_reason',
    'vp_comments',
    'signature_added',
];
```

#### Relationships

```php
// Crew member who submitted the request
public function crew(): BelongsTo
{
    return $this->belongsTo(User::class, 'crew_id');
}

// EA who processed the request  
public function processedBy(): BelongsTo
{
    return $this->belongsTo(User::class, 'processed_by');
}

// VP who approved/disapproved the request
public function approvedBy(): BelongsTo
{
    return $this->belongsTo(User::class, 'approved_by');
}
```

#### Key Methods

**ID Generation:**
```php
public static function generateJobDescriptionId(): string
{
    // Generates: JD-2025-001, JD-2025-002, etc.
    $year = date('Y');
    $latestRequest = static::withTrashed()
        ->where('id', 'like', "JD-{$year}-%")
        ->orderByDesc('id')
        ->first();
    
    $nextNumber = $latestRequest ? ((int) substr($latestRequest->id, -3)) + 1 : 1;
    
    return sprintf('JD-%s-%03d', $year, $nextNumber);
}
```

**Memo Number Generation:**
```php
public function generateMemoNumber(): string
{
    // Generates: NYK-JD-2025-001, NYK-JD-2025-002, etc.
    if ($this->memo_no) {
        return $this->memo_no;
    }

    $year = date('Y');
    $latestMemo = static::withTrashed()
        ->where('memo_no', 'like', "NYK-JD-{$year}-%")
        ->orderByDesc('memo_no')
        ->first();
    
    $nextNumber = $latestMemo ? ((int) substr($latestMemo->memo_no, -3)) + 1 : 1;
    
    $memoNo = sprintf('NYK-JD-%s-%03d', $year, $nextNumber);
    $this->update(['memo_no' => $memoNo]);
    
    return $memoNo;
}
```

**Business Logic Methods:**
```php
public function canBeProcessed(): bool
{
    return $this->status === 'pending';
}

public function canBeApproved(): bool
{
    return $this->status === 'ready_for_approval';
}

public function canBeDownloaded(): bool
{
    return $this->status === 'approved' && $this->signature_added;
}
```

**Purpose Formatting:**
```php
public function getFormattedPurposeAttribute(): string
{
    $purposes = [
        'SSS' => 'Social Security System (SSS)',
        'PAG_IBIG' => 'Pag-Ibig Fund', 
        'PHILHEALTH' => 'PhilHealth',
        'VISA' => 'VISA Application',
    ];

    $basePurpose = $purposes[$this->purpose] ?? $this->purpose;

    if ($this->purpose === 'VISA' && $this->visa_type) {
        $visaTypes = [
            'TOURIST' => 'Tourist Visa',
            'BUSINESS' => 'Business Visa',
            'WORK' => 'Work Visa',
            'TRANSIT' => 'Transit Visa',
            'STUDENT' => 'Student Visa',
            'FAMILY' => 'Family/Dependent Visa',
            'SEAMAN' => "Seaman's Visa",
        ];
        
        return $visaTypes[$this->visa_type] ?? "{$basePurpose} ({$this->visa_type})";
    }

    return $basePurpose;
}
```

#### Query Scopes

```php
// Filter by status
$pendingRequests = JobDescriptionRequest::pending()->get();
$inProgressRequests = JobDescriptionRequest::inProgress()->get();
$readyForApproval = JobDescriptionRequest::readyForApproval()->get();
$approved = JobDescriptionRequest::approved()->get();
$disapproved = JobDescriptionRequest::disapproved()->get();

// Filter by crew member
$crewRequests = JobDescriptionRequest::forCrew($crewId)->get();
```

## 🔄 Status Workflow

The request follows a linear workflow through these states:

```
PENDING → IN_PROGRESS → READY_FOR_APPROVAL → APPROVED/DISAPPROVED
```

### Status Transitions

1. **pending** → **in_progress**
   - Triggered when EA starts processing
   - Sets `processed_by` and `processed_date`

2. **in_progress** → **ready_for_approval**
   - Triggered when EA completes document generation
   - Requires `memo_no`

3. **ready_for_approval** → **approved**
   - Triggered by VP approval
   - Sets `approved_by`, `approved_date`, and `signature_added`
   - Optional `vp_comments`

4. **ready_for_approval** → **disapproved**
   - Triggered by VP rejection
   - Requires `disapproval_reason`
   - Sets `approved_by` and `approved_date`

## 📝 Business Rules & Validation

### Purpose-Specific Rules

1. **VISA Requests**: Must specify `visa_type` when `purpose = 'VISA'`
2. **Non-VISA Requests**: `visa_type` must be NULL
3. **Document Generation**: Requires memo number to be generated
4. **Approval Process**: VP signature required for approved status

### Validation Constraints

```php
// In migration or validation rules:
CONSTRAINT chk_visa_type CHECK (
    (purpose = 'VISA' AND visa_type IS NOT NULL) OR 
    (purpose != 'VISA' AND visa_type IS NULL)
),
CONSTRAINT chk_approval_data CHECK (
    (status = 'approved' AND approved_by IS NOT NULL AND approved_date IS NOT NULL) OR
    (status = 'disapproved' AND disapproval_reason IS NOT NULL) OR
    (status NOT IN ('approved', 'disapproved'))
)
```

## 🎭 Data Types & Enums

### Purpose Types
- `SSS` - Social Security System
- `PAG_IBIG` - Pag-Ibig Fund  
- `PHILHEALTH` - PhilHealth
- `VISA` - VISA Application

### VISA Types (when purpose = 'VISA')
- `TOURIST` - Tourist Visa
- `BUSINESS` - Business Visa
- `WORK` - Work Visa
- `TRANSIT` - Transit Visa
- `STUDENT` - Student Visa
- `FAMILY` - Family/Dependent Visa
- `SEAMAN` - Seaman's Visa

### Status Types
- `pending` - Request submitted, awaiting EA review
- `in_progress` - EA is processing the request
- `ready_for_approval` - Awaiting VP approval
- `approved` - Approved and ready for download
- `disapproved` - Rejected with reason

## 🌱 Database Seeding

The `JobDescriptionRequestSeeder` creates sample data for development:

- **2 Pending requests** - Different purposes (SSS, VISA with Seaman type)
- **1 In-progress request** - Pag-Ibig purpose with EA processing data
- **1 Ready for approval** - PhilHealth purpose with memo number
- **1 Approved request** - Tourist VISA with full approval workflow completed

### Running Seeds

```bash
# Run all seeders including job description requests
php artisan migrate:refresh --seed

# Run only the job description seeder
php artisan db:seed --class=JobDescriptionRequestSeeder
```

## 🔌 API Integration Points

The model is designed to integrate seamlessly with the frontend module's expected API endpoints:

### Expected API Routes Structure

```php
// routes/api.php

// Crew endpoints
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/job-descriptions/request', [JobDescriptionController::class, 'store']);
    Route::get('/job-descriptions/crew/{crewId}', [JobDescriptionController::class, 'crewRequests']);
    Route::get('/job-descriptions/{id}/download', [JobDescriptionController::class, 'download']);
});

// EA endpoints (with role middleware)
Route::middleware(['auth:sanctum', 'role:ea'])->group(function () {
    Route::get('/job-descriptions/pending', [JobDescriptionController::class, 'pending']);
    Route::put('/job-descriptions/{id}/process', [JobDescriptionController::class, 'process']);
    Route::post('/job-descriptions/{id}/generate-pdf', [JobDescriptionController::class, 'generatePdf']);
});

// VP endpoints (with role middleware)  
Route::middleware(['auth:sanctum', 'role:vp'])->group(function () {
    Route::get('/job-descriptions/approval', [JobDescriptionController::class, 'forApproval']);
    Route::put('/job-descriptions/{id}/approve', [JobDescriptionController::class, 'approve']);
    Route::put('/job-descriptions/{id}/disapprove', [JobDescriptionController::class, 'disapprove']);
});
```

### Sample Controller Methods

```php
class JobDescriptionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'purpose' => 'required|in:SSS,PAG_IBIG,PHILHEALTH,VISA',
            'visa_type' => 'required_if:purpose,VISA|in:TOURIST,BUSINESS,WORK,TRANSIT,STUDENT,FAMILY,SEAMAN',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['crew_id'] = auth()->id();

        $request = JobDescriptionRequest::create($validated);

        return response()->json($request->load('crew'), 201);
    }

    public function pending()
    {
        $requests = JobDescriptionRequest::pending()
            ->with(['crew', 'processedBy'])
            ->orderBy('created_at')
            ->get();

        return response()->json($requests);
    }

    public function approve(Request $request, string $id)
    {
        $jobRequest = JobDescriptionRequest::findOrFail($id);
        
        if (!$jobRequest->canBeApproved()) {
            return response()->json(['error' => 'Request cannot be approved in current status'], 400);
        }

        $validated = $request->validate([
            'vp_comments' => 'nullable|string|max:1000',
            'signature_added' => 'boolean',
        ]);

        $jobRequest->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_date' => now(),
            'vp_comments' => $validated['vp_comments'] ?? null,
            'signature_added' => $validated['signature_added'] ?? true,
        ]);

        return response()->json($jobRequest->load(['crew', 'approvedBy']));
    }
}
```

## 📧 Email Notifications Integration

The module is ready for email integration. Recommended notification triggers:

```php
// In JobDescriptionRequest model or observers

// When request is submitted
public function notifyEAOfNewRequest()
{
    Mail::to($eaUsers)->send(new NewJobDescriptionRequestMail($this));
}

// When request is approved
public function notifyCrewOfApproval()
{
    Mail::to($this->crew->email)->send(new JobDescriptionApprovedMail($this));
}

// When request is disapproved
public function notifyCrewOfDisapproval()
{
    Mail::to($this->crew->email)->send(new JobDescriptionDisapprovedMail($this));
}
```

## 🔍 Query Examples

### Common Queries

```php
// Get all pending requests for EA dashboard
$pendingRequests = JobDescriptionRequest::pending()
    ->with(['crew' => function($q) { 
        $q->select('id', 'first_name', 'last_name', 'crew_id'); 
    }])
    ->orderBy('created_at')
    ->get();

// Get crew member's request history
$crewHistory = JobDescriptionRequest::forCrew($crewId)
    ->with(['processedBy', 'approvedBy'])
    ->orderByDesc('created_at')
    ->get();

// Get requests ready for VP approval
$readyForApproval = JobDescriptionRequest::readyForApproval()
    ->with(['crew', 'processedBy'])
    ->orderBy('processed_date')
    ->get();

// Get approved requests for download
$downloadableRequests = JobDescriptionRequest::approved()
    ->where('signature_added', true)
    ->forCrew($crewId)
    ->orderByDesc('approved_date')
    ->get();

// Search requests by memo number
$request = JobDescriptionRequest::where('memo_no', 'NYK-JD-2025-001')->first();

// Get processing statistics
$stats = [
    'pending' => JobDescriptionRequest::pending()->count(),
    'in_progress' => JobDescriptionRequest::inProgress()->count(),
    'ready_for_approval' => JobDescriptionRequest::readyForApproval()->count(),
    'approved_today' => JobDescriptionRequest::approved()
        ->whereDate('approved_date', today())
        ->count(),
];
```

## 🧪 Testing Examples

### Feature Tests

```php
public function test_crew_can_submit_job_description_request()
{
    $crew = User::factory()->crew()->create();
    
    $requestData = [
        'purpose' => 'SSS',
        'notes' => 'Need for loan application',
    ];

    $response = $this->actingAs($crew)
        ->postJson('/api/job-descriptions/request', $requestData);

    $response->assertStatus(201);
    $this->assertDatabaseHas('job_description_requests', [
        'crew_id' => $crew->id,
        'purpose' => 'SSS',
        'status' => 'pending',
    ]);
}

public function test_visa_request_requires_visa_type()
{
    $crew = User::factory()->crew()->create();
    
    $response = $this->actingAs($crew)
        ->postJson('/api/job-descriptions/request', [
            'purpose' => 'VISA',
            // Missing visa_type
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['visa_type']);
}
```

### Unit Tests

```php
public function test_generates_unique_request_ids()
{
    $request1 = JobDescriptionRequest::factory()->create();
    $request2 = JobDescriptionRequest::factory()->create();
    
    $this->assertNotEquals($request1->id, $request2->id);
    $this->assertMatchesRegularExpression('/^JD-\d{4}-\d{3}$/', $request1->id);
}

public function test_formatted_purpose_includes_visa_type()
{
    $request = JobDescriptionRequest::factory()->create([
        'purpose' => 'VISA',
        'visa_type' => 'SEAMAN',
    ]);
    
    $this->assertEquals("Seaman's Visa", $request->formatted_purpose);
}
```

## 🔒 Security Considerations

1. **Access Control**: Ensure crew members can only access their own requests
2. **Role Validation**: Verify EA/VP roles before allowing processing/approval actions
3. **Input Sanitization**: Validate and sanitize all user inputs, especially notes and comments
4. **Audit Trail**: All changes are logged through the `modified_by` field
5. **Soft Deletes**: Records are never permanently deleted, maintaining audit history

## 📈 Performance Considerations

1. **Database Indexes**: Key indexes on `crew_id`, `status`, `created_at`, and `processed_by`
2. **Eager Loading**: Use `with()` to avoid N+1 queries when loading relationships
3. **Pagination**: Implement pagination for large result sets in admin interfaces
4. **Caching**: Consider caching request counts and statistics for dashboard displays

## 🚀 Future Enhancements

1. **File Attachments**: Support for uploading additional documents
2. **Request Templates**: Pre-filled templates based on crew contract data
3. **Bulk Operations**: Allow EA to process multiple requests simultaneously
4. **Advanced Analytics**: Request processing time metrics and reporting
5. **Mobile API**: Optimized endpoints for mobile applications
6. **Multi-language Support**: Localized purpose types and status descriptions

## 🐛 Troubleshooting

### Common Issues

**Auto-increment ID conflicts:**
```php
// If manual IDs cause conflicts, reset the generator:
$latestId = JobDescriptionRequest::withTrashed()->max('id');
// Then continue with normal operations
```

**Status transition errors:**
```php
// Always check business rules before status changes:
if (!$request->canBeProcessed()) {
    throw new InvalidStatusTransitionException();
}
```

**Foreign key constraint violations:**
```php
// Ensure referenced users exist and have proper roles:
$ea = User::where('is_crew', false)->where('job_designation_id', $eaDesignationId)->first();
if (!$ea) {
    throw new NoEAAvailableException();
}
```

## 📞 Support

For technical support or questions about the Job Description module implementation, please refer to the main project documentation or contact the development team.

---

*This module integrates seamlessly with the frontend Job Description components and follows Laravel best practices for maintainable, scalable code.*