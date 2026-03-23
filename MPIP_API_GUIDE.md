# MPIP API Developer Guide

## Table of Contents

1. [How It Works](#how-it-works)
2. [Local Setup](#local-setup)
3. [Testing the API](#testing-the-api)
4. [Production Setup](#production-setup)
5. [Troubleshooting](#troubleshooting)

---

## 1. How It Works

MPIP pushes data into the Crew Portal by calling three webhook endpoints. Each call is authenticated with a shared secret token in the `Authorization` header.

```
MPIP System ──POST──► /api/mpip/crew/sync        (crew profile data)
             ──POST──► /api/mpip/contracts/sync   (contract records)
             ──POST──► /api/mpip/wages/sync        (wage scale table)
```

### Authentication Flow

Every request must include:

```
Authorization: Bearer <MPIP_SHARED_SECRET>
Content-Type: application/json
```

The server compares the token against the `MPIP_SHARED_SECRET` environment variable using a constant-time comparison (`hash_equals`) to prevent timing attacks.

### Data Matching Logic

MPIP does not know internal database IDs. Matching is done via human-readable keys:

| MPIP sends         | Matched against     | Resolved to      |
| ------------------ | ------------------- | ---------------- |
| `email`            | `users.email`       | `user_id`        |
| `rank_code`        | `ranks.code`        | `rank_id`        |
| `vessel_name`      | `vessels.name`      | `vessel_id`      |
| `vessel_type_name` | `vessel_types.name` | `vessel_type_id` |

If a lookup fails (e.g. unknown `rank_code`), that FK is stored as `NULL` and the record is still saved. If `email` is not found, that crew/contract entry is **skipped** (logged in the `skipped` array of the response).

### Idempotency

All three endpoints are safe to call multiple times with the same data:

- `crew/sync` — updates existing profile fields
- `contracts/sync` — upserts on `contract_number` (update if exists, insert if new)
- `wages/sync` — upserts on `(rank_id, vessel_type_id, effective_date)`

### `modified_by` Tracking

All records touched by MPIP webhooks have `modified_by = 'MPIP API'`. This distinguishes them from admin-edited records in the audit trail.

---

## 2. Local Setup

### Step 1 — Run the migrations

```bash
cd CREW_PORTAL_BACKEND
php artisan migrate
```

This creates the `wage_scales` table and adds the new MPIP columns to `contracts`.

### Step 2 — Set the shared secret

Open `CREW_PORTAL_BACKEND/.env` and add:

```env
MPIP_SHARED_SECRET=your-local-test-secret
```

You can use any string for local testing, e.g. `mpip-local-secret-123`.

### Step 3 — Clear config cache

```bash
php artisan config:clear
```

### Step 4 — Start the server

```bash
composer dev
# or
php artisan serve
```

The API will be available at `http://localhost:8000/api/mpip/...`

---

## 3. Testing the API

### Using cURL

Replace `<SECRET>` with whatever you set in `MPIP_SHARED_SECRET`.

#### Test Authentication (wrong token → 401)

```bash
curl -X POST http://localhost:8000/api/mpip/crew/sync \
  -H "Authorization: Bearer wrong-token" \
  -H "Content-Type: application/json" \
  -d '{"crew":[]}'
```

Expected:

```json
{
    "success": false,
    "message": "Unauthorized. Invalid or missing MPIP shared secret."
}
```

---

#### 3.1 Crew Sync

The crew sync accepts a comprehensive payload covering all sections. All sections except `email` are optional — only send what MPIP has.

**Minimal request (profile only):**

```bash
curl -X POST http://localhost:8000/api/mpip/crew/sync \
  -H "Authorization: Bearer <SECRET>" \
  -H "Content-Type: application/json" \
  -d '{
    "crew": [
      {
        "email": "crew@example.com",
        "is_industrial": true,
        "profile": {
          "first_name": "Juan",
          "middle_name": "Santos",
          "last_name": "Dela Cruz",
          "birth_date": "1990-04-15",
          "gender": "M",
          "nationality": "Filipino",
          "civil_status": "married",
          "rank_code": "MAS",
          "fleet_name": "NYK Bulk Fleet",
          "company_name": "NYK-Fil Ship Management, Inc."
        }
      }
    ]
  }'
```

**Full request (all sections):**

```bash
curl -X POST http://localhost:8000/api/mpip/crew/sync \
  -H "Authorization: Bearer <SECRET>" \
  -H "Content-Type: application/json" \
  -d '{
    "crew": [
      {
        "email": "crew@example.com",
        "is_industrial": true,
        "profile": {
          "first_name": "Juan",
          "middle_name": "Santos",
          "last_name": "Dela Cruz",
          "suffix": null,
          "birth_date": "1990-04-15",
          "birth_place": "Manila",
          "gender": "M",
          "nationality": "Filipino",
          "civil_status": "married",
          "religion": "Roman Catholic",
          "blood_type": "O+",
          "rank_code": "MAS",
          "fleet_name": "NYK Bulk Fleet",
          "company_name": "NYK-Fil Ship Management, Inc."
        },
        "contact": {
          "mobile_number": "+639171234567",
          "emergency_contact_name": "Maria Dela Cruz",
          "emergency_contact_phone": "+639189876543",
          "emergency_contact_relationship": "Spouse"
        },
        "permanent_address": {
          "full_address": "123 Main St, Makati City 1200",
          "street_address": "123 Main St",
          "zip_code": "1200"
        },
        "current_address": {
          "full_address": "456 Roxas Blvd, Manila 1000",
          "street_address": "456 Roxas Blvd",
          "zip_code": "1000"
        },
        "employment": {
          "crew_status": "on_board",
          "hire_status": "re_hire",
          "hire_date": "2020-01-15",
          "passport_number": "P1234567A",
          "passport_expiry": "2028-01-15",
          "seaman_book_number": "SB1234567",
          "seaman_book_expiry": "2027-06-30",
          "basic_salary": 3000.00
        },
        "education": [
          {
            "school_name": "Philippine Merchant Marine Academy",
            "date_graduated": "2012-03-01",
            "degree": "BS Marine Transportation",
            "education_level": "college"
          }
        ],
        "allotees": [
          {
            "name": "Maria Santos Dela Cruz",
            "relationship": "spouse",
            "mobile_number": "+639189876543",
            "email": "maria@example.com",
            "date_of_birth": "1992-08-20",
            "gender": "F",
            "is_primary": true,
            "is_emergency_contact": true
          }
        ],
        "travel_documents": [
          {
            "document_type_name": "Passport",
            "id_no": "P1234567A",
            "place_of_issue": "Manila",
            "date_of_issue": "2018-01-15",
            "expiration_date": "2028-01-15",
            "file_path": "travel_documents/123/passport.pdf",
            "file_ext": "pdf"
          }
        ],
        "employment_documents": [
          {
            "document_type_name": "POEA Contract",
            "document_number": "POEA-2026-001234",
            "file_path": "employment_documents/123/poea_contract.pdf",
            "file_ext": "pdf"
          }
        ],
        "certificates": [
          {
            "certificate_code": "STCW-BST",
            "certificate_no": "BST-2020-00123",
            "issued_by": "MAAP",
            "date_issued": "2020-06-01",
            "expiry_date": "2025-06-01",
            "file_path": "certificates/123/bst.pdf",
            "file_ext": "pdf"
          }
        ],
        "programs": [
          {
            "program_name": "NYK-Fil Cadet Program",
            "batch": "Batch 2012"
          }
        ]
      }
    ]
  }'
```

Expected (if `crew@example.com` exists as a crew user):

```json
{
    "success": true,
    "message": "Crew sync completed.",
    "summary": { "updated": 1, "skipped": 0, "errors": 0 },
    "details": {
        "updated": ["crew@example.com"],
        "skipped": [],
        "errors": []
    }
}
```

Expected (if email not found):

```json
{
    "success": true,
    "message": "Crew sync completed.",
    "summary": { "updated": 0, "skipped": 1, "errors": 0 },
    "details": {
        "updated": [],
        "skipped": [
            {
                "email": "crew@example.com",
                "reason": "No crew member found with this email."
            }
        ],
        "errors": []
    }
}
```

> **Note on `education` and `allotees`:** These sections are **destructive** — sending them replaces all existing records for that crew member. If you only want to update the profile, omit these sections entirely.

---

#### 3.2 Contracts Sync

```bash
curl -X POST http://localhost:8000/api/mpip/contracts/sync \
  -H "Authorization: Bearer <SECRET>" \
  -H "Content-Type: application/json" \
  -d '{
    "contracts": [
      {
        "contract_number": "NYK-2026-001",
        "email": "crew@example.com",
        "vessel_name": "MV Horizon",
        "rank_code": "MAS",
        "port_of_departure": "Manila",
        "port_of_arrival": "Rotterdam",
        "contract_start_date": "2026-01-01",
        "contract_end_date": "2026-07-01",
        "duration_months": 6,
        "departure_date": "2026-01-15",
        "arrival_date": "2026-01-20",
        "basic_wage": 3000.00,
        "fixed_overtime": 750.00,
        "leave_pay": 450.00,
        "subsistence_allowance": 300.00,
        "vacation_leave_conversion": 200.00,
        "total_guaranteed_monthly": 4700.00,
        "currency": "USD",
        "contract_status": "active",
        "remarks": null
      }
    ]
  }'
```

Expected:

```json
{
    "success": true,
    "message": "Contracts sync completed.",
    "summary": { "upserted": 1, "skipped": 0, "errors": 0 },
    "details": {
        "upserted": ["NYK-2026-001"],
        "skipped": [],
        "errors": []
    }
}
```

---

#### 3.3 Wage Scale Sync

```bash
curl -X POST http://localhost:8000/api/mpip/wages/sync \
  -H "Authorization: Bearer <SECRET>" \
  -H "Content-Type: application/json" \
  -d '{
    "wages": [
      {
        "rank_code": "MAS",
        "vessel_type_name": "Bulk Carrier",
        "effective_date": "2026-01-01",
        "basic_wage": 3000.00,
        "fixed_overtime": 750.00,
        "leave_pay": 450.00,
        "subsistence_allowance": 300.00,
        "vacation_leave_conversion": 200.00,
        "total_guaranteed_monthly": 4700.00,
        "currency": "USD"
      }
    ]
  }'
```

Expected:

```json
{
    "success": true,
    "message": "Wage scale sync completed.",
    "summary": { "upserted": 1, "errors": 0 },
    "details": { "errors": [] }
}
```

---

### Using Postman

1. Create a new **POST** request for each endpoint.
2. Under **Headers**, add:
    - `Authorization` → `Bearer your-local-test-secret`
    - `Content-Type` → `application/json`
3. Under **Body** → **raw** → **JSON**, paste the request body examples above.
4. Send and verify the response.

**Tip:** Save the three requests as a Postman Collection named "MPIP Webhooks" for easy re-testing.

---

### Checking the Database After Testing

After running the syncs, verify records were created/updated:

```bash
cd CREW_PORTAL_BACKEND
php artisan tinker
```

```php
// ── Crew sync verification ──────────────────────────────────────────────

// Find a crew user and check all their synced data
$user = App\Models\User::where('email', 'crew@example.com')->first();

// Basic profile
$user->profile;
$user->profile->rank;        // resolved from rank_code
$user->profile->fleet;       // resolved from fleet_name
$user->profile->company;     // resolved from company_name

// Contact & addresses
$user->contacts;
$user->contacts->permanentAddress;
$user->contacts->currentAddress;

// Employment
$user->employment;

// Education records
$user->educations;

// Allotees (beneficiaries)
$user->allotees;

// Travel documents (uses crew_id from profile)
App\Models\TravelDocument::where('crew_id', $user->profile->crew_id)->get();

// Employment documents
App\Models\EmploymentDocument::where('crew_id', $user->profile->crew_id)->get();

// Certificates
App\Models\CrewCertificate::where('crew_id', $user->profile->crew_id)->get();

// Programs
$user->programEmployments()->with('program')->get();

// ── Contracts sync verification ──────────────────────────────────────────
App\Models\Contract::where('modified_by', 'MPIP API')->with('rank', 'vessel')->get();

// ── Wage scale sync verification ─────────────────────────────────────────
App\Models\WageScale::with('rank', 'vesselType')->get();

// ── All records touched by MPIP ───────────────────────────────────────────
App\Models\UserProfile::where('modified_by', 'MPIP API')->count();
App\Models\Contract::where('modified_by', 'MPIP API')->count();
App\Models\WageScale::where('modified_by', 'MPIP API')->count();
```

---

## 4. Production Setup

### Step 1 — Generate a strong secret

```bash
openssl rand -hex 32
```

Example output: `a3f9c2d1e8b74f6a5c0d2e1f8b7a4c9d3e6f2a1b8c5d7e0f4a3b2c1d9e8f7a6`

### Step 2 — Set the environment variable

On your production server, add to `.env`:

```env
MPIP_SHARED_SECRET=a3f9c2d1e8b74f6a5c0d2e1f8b7a4c9d3e6f2a1b8c5d7e0f4a3b2c1d9e8f7a6
```

> **Keep this secret safe.** Never commit it to version control. Store it in your server's environment or a secrets manager.

### Step 3 — Share the secret with the MPIP team

Provide the MPIP team with:

- The secret value
- The three endpoint URLs:
    - `https://your-domain.com/api/mpip/crew/sync`
    - `https://your-domain.com/api/mpip/contracts/sync`
    - `https://your-domain.com/api/mpip/wages/sync`

### Step 4 — Run migrations on production

```bash
php artisan migrate --force
php artisan config:clear
php artisan cache:clear
```

### Step 5 — HTTPS only

The MPIP endpoints must be served over HTTPS in production. Sending the shared secret over plain HTTP exposes it to interception. Ensure your web server (Apache/Nginx) enforces HTTPS and has a valid SSL certificate.

### Step 6 — Verify the ranks and vessel names match

MPIP resolves lookups by `rank_code` and `vessel_name`. Before going live, confirm with the MPIP team what codes/names they will use and ensure they match the values in your database:

```bash
php artisan tinker

# List all rank codes
App\Models\Rank::whereNotNull('code')->pluck('name', 'code');

# List all vessel names
App\Models\Vessel::pluck('name');

# List all vessel type names
App\Models\VesselType::pluck('name');
```

Share this list with the MPIP team so they use the exact same values.

---

## 5. Troubleshooting

### 401 Unauthorized

**Cause:** The `Authorization` header is missing or the token doesn't match.

**Fix:**

- Confirm `MPIP_SHARED_SECRET` is set in `.env`.
- Run `php artisan config:clear` after changing `.env`.
- Verify the token in the header matches exactly (no extra spaces or newlines).

---

### 503 Service Unavailable — "MPIP integration is not configured"

**Cause:** `MPIP_SHARED_SECRET` is not set in `.env`.

**Fix:** Add `MPIP_SHARED_SECRET=your-secret` to `.env` and run `php artisan config:clear`.

---

### 422 Validation Failed

**Cause:** Required fields are missing or have wrong types.

**Fix:** Check the `errors` object in the response — it lists exactly which fields failed and why.

Example:

```json
{
    "errors": {
        "contracts.0.contract_start_date": [
            "The contracts.0.contract_start_date field is required."
        ]
    }
}
```

---

### Records are skipped (email not found)

**Cause:** The `email` sent by MPIP doesn't exist in the `users` table as a crew member (`is_crew = 1`).

**Fix:**

- Confirm the crew member is registered in the system with that exact email.
- The crew member must have `is_crew = 1` — MPIP cannot create new users.
- Check for case differences or extra whitespace in the email.

---

### Rank / Vessel / VesselType shows as NULL after sync

**Cause:** The `rank_code` or `vessel_name` sent by MPIP doesn't match any record in the database.

**Fix:**

- Run the lookup queries in [Step 6 of Production Setup](#step-6--verify-the-ranks-and-vessel-names-match).
- Share the exact codes/names with the MPIP team to align.

---

### CORS error when calling from MPIP server

**Cause:** MPIP's server is not in the allowed CORS origins.

**Fix:** Add the MPIP server's origin to `config/cors.php`:

```php
'allowed_origins' => [
    env('FRONTEND_URL', 'http://localhost:3000'),
    'https://mpip-server.example.com',  // add this
],
```

Then run `php artisan config:clear`.

---

### Checking logs for errors

```bash
tail -f storage/logs/laravel.log
```

All unhandled exceptions during a sync are caught per-record and returned in the `errors` array of the response. The transaction rolls back only if an unhandled top-level exception occurs.
