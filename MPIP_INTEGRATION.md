# MPIP Integration Documentation

## Overview

This document describes the integration between the NYK-FIL Crew Portal (Super App) and the MPIP (Maritime Pre-Employment and Information Platform) system. The integration covers crew data synchronization, contract management, and wage table updates.

---

## Part 1: Database Structure

### `users` table

| Column             | Type            | Nullable | PK/FK               | Description                             |
| ------------------ | --------------- | -------- | ------------------- | --------------------------------------- |
| id                 | BIGINT UNSIGNED | No       | PK                  | Auto-increment primary key              |
| email              | VARCHAR(255)    | No       |                     | Unique login email                      |
| is_crew            | TINYINT(1)      | Yes      |                     | 1 = crew, 0 = admin, NULL = unspecified |
| is_industrial      | TINYINT(1)      | Yes      |                     | 1 = industrial crew (goes through MPIP) |
| department_id      | BIGINT UNSIGNED | Yes      | FK → departments.id | Admin department assignment             |
| email_verified_at  | TIMESTAMP       | Yes      |                     | Email verification timestamp            |
| last_login_at      | TIMESTAMP       | Yes      |                     | Last successful login                   |
| last_login_ip      | VARCHAR(45)     | Yes      |                     | IP address of last login                |
| device_fingerprint | VARCHAR(255)    | Yes      |                     | Device fingerprint for binding          |
| device_name        | VARCHAR(255)    | Yes      |                     | Human-readable device name              |
| modified_by        | VARCHAR(255)    | Yes      |                     | Last editor (admin name or "MPIP API")  |
| created_at         | TIMESTAMP       | Yes      |                     |                                         |
| updated_at         | TIMESTAMP       | Yes      |                     |                                         |
| deleted_at         | TIMESTAMP       | Yes      |                     | Soft delete                             |

---

### `user_profiles` table

| Column       | Type            | Nullable | PK/FK             | Description                            |
| ------------ | --------------- | -------- | ----------------- | -------------------------------------- |
| id           | BIGINT UNSIGNED | No       | PK                | Auto-increment primary key             |
| user_id      | BIGINT UNSIGNED | No       | FK → users.id     | Owning user                            |
| crew_id      | VARCHAR(255)    | Yes      |                   | Unique crew identifier string          |
| first_name   | VARCHAR(255)    | Yes      |                   | First name                             |
| middle_name  | VARCHAR(255)    | Yes      |                   | Middle name                            |
| last_name    | VARCHAR(255)    | Yes      |                   | Last name                              |
| suffix       | VARCHAR(50)     | Yes      |                   | Name suffix (Jr., Sr., etc.)           |
| birth_date   | DATE            | Yes      |                   | Date of birth                          |
| birth_place  | VARCHAR(255)    | Yes      |                   | Place of birth                         |
| age          | INT             | Yes      |                   | Auto-calculated from birth_date        |
| gender       | VARCHAR(10)     | Yes      |                   | Gender (M / F)                         |
| nationality  | VARCHAR(100)    | Yes      |                   | Nationality string                     |
| civil_status | VARCHAR(50)     | Yes      |                   | civil status (single, married, etc.)   |
| religion     | VARCHAR(100)    | Yes      |                   | Religion                               |
| blood_type   | VARCHAR(10)     | Yes      |                   | Blood type (A+, O-, etc.)              |
| rank_id      | BIGINT UNSIGNED | Yes      | FK → ranks.id     | Current rank                           |
| fleet_id     | BIGINT UNSIGNED | Yes      | FK → fleets.id    | Assigned fleet                         |
| company_id   | BIGINT UNSIGNED | Yes      | FK → companies.id | Manning company                        |
| modified_by  | VARCHAR(255)    | Yes      |                   | Last editor (admin name or "MPIP API") |
| created_at   | TIMESTAMP       | Yes      |                   |                                        |
| updated_at   | TIMESTAMP       | Yes      |                   |                                        |
| deleted_at   | TIMESTAMP       | Yes      |                   | Soft delete                            |

---

### `contracts` table

| Column                    | Type            | Nullable | PK/FK           | Description                                         |
| ------------------------- | --------------- | -------- | --------------- | --------------------------------------------------- |
| id                        | BIGINT UNSIGNED | No       | PK              | Auto-increment primary key                          |
| contract_number           | VARCHAR(100)    | No       |                 | Unique contract reference (upsert key for MPIP)     |
| user_id                   | BIGINT UNSIGNED | No       | FK → users.id   | Crew member                                         |
| vessel_id                 | BIGINT UNSIGNED | Yes      | FK → vessels.id | Assigned vessel                                     |
| rank_id                   | BIGINT UNSIGNED | Yes      | FK → ranks.id   | Rank at time of contract                            |
| departure_date            | DATE            | Yes      |                 | Actual departure date                               |
| arrival_date              | DATE            | Yes      |                 | Actual arrival date                                 |
| duration_months           | INT             | Yes      |                 | Contract duration in months                         |
| contract_start_date       | DATE            | Yes      |                 | Contract start date                                 |
| contract_end_date         | DATE            | Yes      |                 | Contract end date (auto-calculated if not provided) |
| port_of_departure         | VARCHAR(255)    | Yes      |                 | Port of departure                                   |
| port_of_arrival           | VARCHAR(255)    | Yes      |                 | Port of arrival                                     |
| basic_wage                | DECIMAL(10,2)   | Yes      |                 | Basic monthly wage                                  |
| fixed_overtime            | DECIMAL(10,2)   | Yes      |                 | Fixed overtime pay                                  |
| leave_pay                 | DECIMAL(10,2)   | Yes      |                 | Leave pay                                           |
| subsistence_allowance     | DECIMAL(10,2)   | Yes      |                 | Subsistence allowance                               |
| vacation_leave_conversion | DECIMAL(10,2)   | Yes      |                 | Vacation leave conversion                           |
| total_guaranteed_monthly  | DECIMAL(10,2)   | Yes      |                 | Total guaranteed monthly compensation               |
| currency                  | VARCHAR(10)     | Yes      |                 | Currency code (default: USD)                        |
| contract_status           | VARCHAR(50)     | Yes      |                 | Status from MPIP (active, completed, cancelled)     |
| remarks                   | TEXT            | Yes      |                 | Free-form remarks from MPIP                         |
| modified_by               | VARCHAR(255)    | Yes      |                 | Last editor (admin name or "MPIP API")              |
| created_at                | TIMESTAMP       | Yes      |                 |                                                     |
| updated_at                | TIMESTAMP       | Yes      |                 |                                                     |
| deleted_at                | TIMESTAMP       | Yes      |                 | Soft delete                                         |

---

### `wage_scales` table

| Column                    | Type            | Nullable | PK/FK                | Description                                   |
| ------------------------- | --------------- | -------- | -------------------- | --------------------------------------------- |
| id                        | BIGINT UNSIGNED | No       | PK                   | Auto-increment primary key                    |
| rank_id                   | BIGINT UNSIGNED | Yes      | FK → ranks.id        | Applicable rank (NULL = applies to all ranks) |
| vessel_type_id            | BIGINT UNSIGNED | Yes      | FK → vessel_types.id | Applicable vessel type (NULL = all types)     |
| effective_date            | DATE            | No       |                      | Date from which this scale is effective       |
| basic_wage                | DECIMAL(10,2)   | No       |                      | Basic monthly wage                            |
| fixed_overtime            | DECIMAL(10,2)   | No       |                      | Fixed overtime pay (default 0)                |
| leave_pay                 | DECIMAL(10,2)   | No       |                      | Leave pay (default 0)                         |
| subsistence_allowance     | DECIMAL(10,2)   | No       |                      | Subsistence allowance (default 0)             |
| vacation_leave_conversion | DECIMAL(10,2)   | No       |                      | Vacation leave conversion (default 0)         |
| total_guaranteed_monthly  | DECIMAL(10,2)   | No       |                      | Total guaranteed monthly compensation         |
| currency                  | VARCHAR(10)     | No       |                      | Currency code (default: USD)                  |
| modified_by               | VARCHAR(255)    | Yes      |                      | Always "MPIP API" for MPIP-sourced records    |
| created_at                | TIMESTAMP       | Yes      |                      |                                               |
| updated_at                | TIMESTAMP       | Yes      |                      |                                               |
| deleted_at                | TIMESTAMP       | Yes      |                      | Soft delete                                   |

**Unique constraint:** `(rank_id, vessel_type_id, effective_date)`

---

### `ranks` table

| Column             | Type            | Nullable | PK/FK                    | Description           |
| ------------------ | --------------- | -------- | ------------------------ | --------------------- |
| id                 | BIGINT UNSIGNED | No       | PK                       |                       |
| rank_department_id | BIGINT UNSIGNED | Yes      | FK → rank_departments.id | Deck / Engine / etc.  |
| rank_type_id       | BIGINT UNSIGNED | Yes      | FK → rank_types.id       | Officer / Rating      |
| name               | VARCHAR(255)    | No       |                          | Full rank name        |
| code               | VARCHAR(50)     | Yes      |                          | Short code (e.g. MAS) |
| modified_by        | VARCHAR(255)    | Yes      |                          |                       |
| created_at         | TIMESTAMP       | Yes      |                          |                       |
| updated_at         | TIMESTAMP       | Yes      |                          |                       |
| deleted_at         | TIMESTAMP       | Yes      |                          | Soft delete           |

---

### `companies` table

| Column      | Type            | Nullable | PK/FK | Description                |
| ----------- | --------------- | -------- | ----- | -------------------------- |
| id          | BIGINT UNSIGNED | No       | PK    | Auto-increment primary key |
| name        | VARCHAR(255)    | No       |       | Company name (unique)      |
| modified_by | VARCHAR(255)    | Yes      |       |                            |
| created_at  | TIMESTAMP       | Yes      |       |                            |
| updated_at  | TIMESTAMP       | Yes      |       |                            |
| deleted_at  | TIMESTAMP       | Yes      |       | Soft delete                |

---

### `vessels` table

| Column         | Type            | Nullable | PK/FK                | Description              |
| -------------- | --------------- | -------- | -------------------- | ------------------------ |
| id             | BIGINT UNSIGNED | No       | PK                   |                          |
| name           | VARCHAR(255)    | No       |                      | Vessel name (lookup key) |
| vessel_type_id | BIGINT UNSIGNED | Yes      | FK → vessel_types.id |                          |
| fleet_id       | BIGINT UNSIGNED | Yes      | FK → fleets.id       |                          |
| prefix         | VARCHAR(50)     | Yes      |                      | e.g. MV, MT              |
| modified_by    | VARCHAR(255)    | Yes      |                      |                          |
| created_at     | TIMESTAMP       | Yes      |                      |                          |
| updated_at     | TIMESTAMP       | Yes      |                      |                          |
| deleted_at     | TIMESTAMP       | Yes      |                      | Soft delete              |

---

### `vessel_types` table

| Column      | Type            | Nullable | PK/FK | Description            |
| ----------- | --------------- | -------- | ----- | ---------------------- |
| id          | BIGINT UNSIGNED | No       | PK    |                        |
| name        | VARCHAR(255)    | No       |       | Type name (lookup key) |
| modified_by | VARCHAR(255)    | Yes      |       |                        |
| created_at  | TIMESTAMP       | Yes      |       |                        |
| updated_at  | TIMESTAMP       | Yes      |       |                        |
| deleted_at  | TIMESTAMP       | Yes      |       | Soft delete            |

---

## Part 2: API Flow

### Routing Logic

| Condition                                                            | Route                                        |
| -------------------------------------------------------------------- | -------------------------------------------- |
| `is_industrial = 1` AND `company_id = NYK-Fil Ship Management, Inc.` | Super App → Shipmate (manual for now) → MPIP |
| `is_industrial = 0`                                                  | Super App → MPIP directly                    |
| `company_id ≠ NYK-Fil Ship Management, Inc.`                         | Super App → MPIP directly                    |

### Integration Steps

**Step 1 — Super App → Web Shipmate**

- Condition: `is_industrial = 1` AND `company_id = "NYK-Fil Ship Management, Inc."`
- Manual process for now; future automation planned.
- Crew profile data is forwarded to Web Shipmate.

**Step 2 — Shipmate → MPIP**

- Shipmate's responsibility.
- Shipmate forwards industrial crew data to MPIP.

**Step 3 — MPIP → Super App (Crew Data)**

- MPIP pushes industrial crew profile updates (`is_industrial = 1`) to the Super App.
- Uses `POST /api/mpip/crew/sync`.

**Step 4 — Super App → MPIP (Non-industrial crew)**

- Condition: `is_industrial = 0`
- Super App pushes non-industrial crew data directly to MPIP.
- _(Outbound endpoint — to be implemented)_

**Step 5 — Super App → MPIP (Non-NYK-Fil company crew)**

- Condition: `company_id ≠ NYK-Fil Ship Management, Inc.`
- Super App pushes these crew directly to MPIP.
- _(Outbound endpoint — to be implemented)_

**Step 6 — MPIP → Super App (Contracts & Wages)**

- MPIP pushes contracts and wage tables for ALL crew to the Super App.
- Uses `POST /api/mpip/contracts/sync` and `POST /api/mpip/wages/sync`.

---

## Part 3: Inbound API Endpoints (MPIP → Super App)

All endpoints are under the prefix `/api/mpip/` and require a shared-secret Bearer token.

### Authentication

All MPIP webhook requests must include:

```
Authorization: Bearer {MPIP_SHARED_SECRET}
Content-Type: application/json
```

The secret is configured via the `MPIP_SHARED_SECRET` environment variable.

---

### 3.1 Crew Data Sync

**`POST /api/mpip/crew/sync`**

Receives comprehensive crew data from MPIP and updates matching crew records in the Super App. Matching is done by `email`. Only existing crew users are updated — new users are NOT created.

All sections (`profile`, `contact`, `permanent_address`, `current_address`, `employment`, `education`, `allotees`, `travel_documents`, `employment_documents`, `certificates`, `programs`) are optional. Only the sections present in the payload are updated.

#### Sync Behavior per Section

| Section                | Behavior                                                             |
| ---------------------- | -------------------------------------------------------------------- |
| `profile`              | Update or create `user_profiles` record                              |
| `contact`              | Update or create `user_contacts` record                              |
| `permanent_address`    | Upsert `addresses` where `type = 'permanent'`; link to contact       |
| `current_address`      | Upsert `addresses` where `type = 'current'`; link to contact         |
| `employment`           | Update or create `user_employment` record                            |
| `education`            | **Replace** all education records (delete + re-insert)               |
| `allotees`             | **Replace** pivot associations; upsert allotee records by email/name |
| `travel_documents`     | Upsert by `crew_id + id_no + travel_document_type_id`                |
| `employment_documents` | Upsert by `crew_id + employment_document_type_id + document_number`  |
| `certificates`         | Upsert by `crew_id + certificate_id + certificate_no`                |
| `programs`             | Upsert by `user_id + program_id`                                     |

#### Request Body

```json
{
    "crew": [
        {
            "email": "juan.dela.cruz@example.com",
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
                "alternate_phone": null,
                "emergency_contact_name": "Maria Dela Cruz",
                "emergency_contact_phone": "+639189876543",
                "emergency_contact_relationship": "Spouse"
            },

            "permanent_address": {
                "full_address": "123 Main St, Brgy. San Antonio, Makati City, Metro Manila 1200",
                "street_address": "123 Main St",
                "zip_code": "1200"
            },

            "current_address": {
                "full_address": "456 Roxas Blvd, Ermita, Manila 1000",
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
                "basic_salary": 3000.0,
                "employment_notes": null
            },

            "education": [
                {
                    "school_name": "Philippine Merchant Marine Academy",
                    "date_graduated": "2012-03-01",
                    "degree": "Bachelor of Science in Marine Transportation",
                    "education_level": "college"
                }
            ],

            "allotees": [
                {
                    "name": "Maria Santos Dela Cruz",
                    "relationship": "spouse",
                    "mobile_number": "+639189876543",
                    "email": "maria@example.com",
                    "address": "123 Main St, Makati",
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
                    "remaining_pages": null,
                    "is_US_VISA": false,
                    "visa_type": null,
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
                    "grade": null,
                    "rank_permitted": "Master",
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
}
```

#### Top-level Fields

| Field                | Type    | Required | Description                                             |
| -------------------- | ------- | -------- | ------------------------------------------------------- |
| crew                 | array   | Yes      | Array of crew objects (min 1)                           |
| email                | string  | Yes      | Crew member's registered email (matching key)           |
| is_industrial        | boolean | No       | Mark crew as industrial (updates `users.is_industrial`) |
| profile              | object  | No       | Basic personal information (see sub-fields below)       |
| contact              | object  | No       | Contact numbers and emergency contact                   |
| permanent_address    | object  | No       | Permanent home address                                  |
| current_address      | object  | No       | Current residential address                             |
| employment           | object  | No       | Employment status, document numbers, salary             |
| education            | array   | No       | Education records — **replaces** existing records       |
| allotees             | array   | No       | Allotees/beneficiaries — **replaces** existing links    |
| travel_documents     | array   | No       | Travel documents — upserted individually                |
| employment_documents | array   | No       | Employment documents — upserted individually            |
| certificates         | array   | No       | Crew certificates — upserted individually               |
| programs             | array   | No       | Program enrollments — upserted individually             |

#### `profile` Fields

| Field        | Type   | Required | Description                                  |
| ------------ | ------ | -------- | -------------------------------------------- |
| first_name   | string | Yes\*    | \*Required when `profile` section is present |
| last_name    | string | Yes\*    | \*Required when `profile` section is present |
| middle_name  | string | No       |                                              |
| suffix       | string | No       |                                              |
| birth_date   | date   | No       | YYYY-MM-DD                                   |
| birth_place  | string | No       |                                              |
| gender       | string | No       | M or F                                       |
| nationality  | string | No       |                                              |
| civil_status | string | No       |                                              |
| religion     | string | No       |                                              |
| blood_type   | string | No       | A+, O-, etc.                                 |
| rank_code    | string | No       | Matched to `ranks.code`                      |
| fleet_name   | string | No       | Matched to `fleets.name`                     |
| company_name | string | No       | Matched to `companies.name`                  |

#### `employment` Fields

| Field              | Type    | Required | Description                   |
| ------------------ | ------- | -------- | ----------------------------- |
| crew_status        | string  | No       | on_board / on_vacation / etc. |
| hire_status        | string  | No       | new_hire / re_hire            |
| hire_date          | date    | No       |                               |
| passport_number    | string  | No       |                               |
| passport_expiry    | date    | No       |                               |
| seaman_book_number | string  | No       |                               |
| seaman_book_expiry | date    | No       |                               |
| basic_salary       | decimal | No       |                               |
| employment_notes   | string  | No       |                               |

#### `allotees[]` Fields

| Field                | Type    | Required | Description                                          |
| -------------------- | ------- | -------- | ---------------------------------------------------- |
| name                 | string  | Yes\*    | \*Required when `allotees` is present                |
| relationship         | string  | No       | spouse / child / parent / sibling / etc.             |
| mobile_number        | string  | No       |                                                      |
| email                | string  | No       | Used as upsert key when present                      |
| address              | string  | No       | Free-form address string                             |
| date_of_birth        | date    | No       |                                                      |
| gender               | string  | No       |                                                      |
| is_primary           | boolean | No       | Marks as primary allotee (updates `user_employment`) |
| is_emergency_contact | boolean | No       |                                                      |

#### `travel_documents[]` Fields

| Field              | Type    | Required | Description                                                   |
| ------------------ | ------- | -------- | ------------------------------------------------------------- |
| document_type_name | string  | Yes\*    | Matched to `travel_document_types.name`                       |
| id_no              | string  | Yes\*    | Document ID / number                                          |
| place_of_issue     | string  | No       |                                                               |
| date_of_issue      | date    | No       |                                                               |
| expiration_date    | date    | No       |                                                               |
| remaining_pages    | integer | No       | For passports                                                 |
| is_US_VISA         | boolean | No       |                                                               |
| visa_type          | string  | No       |                                                               |
| file_path          | string  | No       | Relative file path (e.g. `travel_documents/123/passport.pdf`) |
| file_ext           | string  | No       | File extension without dot (e.g. `pdf`, `jpg`)                |

#### `employment_documents[]` Fields

| Field              | Type   | Required | Description                                                       |
| ------------------ | ------ | -------- | ----------------------------------------------------------------- |
| document_type_name | string | Yes\*    | Matched to `employment_document_types.name`                       |
| document_number    | string | No       |                                                                   |
| file_path          | string | No       | Relative file path (e.g. `employment_documents/123/contract.pdf`) |
| file_ext           | string | No       | File extension without dot (e.g. `pdf`, `jpg`)                    |

#### `certificates[]` Fields

| Field            | Type   | Required | Description                                          |
| ---------------- | ------ | -------- | ---------------------------------------------------- |
| certificate_code | string | Yes\*    | Matched to `certificates.code`                       |
| grade            | string | No       |                                                      |
| rank_permitted   | string | No       |                                                      |
| certificate_no   | string | No       | Document number on the physical certificate          |
| issued_by        | string | No       | Issuing authority                                    |
| date_issued      | date   | No       |                                                      |
| expiry_date      | date   | No       |                                                      |
| file_path        | string | No       | Relative file path (e.g. `certificates/123/bst.pdf`) |
| file_ext         | string | No       | File extension without dot (e.g. `pdf`, `jpg`)       |

#### `programs[]` Fields

| Field        | Type   | Required | Description                          |
| ------------ | ------ | -------- | ------------------------------------ |
| program_name | string | Yes\*    | Matched to `programs.name`           |
| batch        | string | No       | Batch identifier (e.g. "Batch 2012") |

#### Success Response (200)

```json
{
    "success": true,
    "message": "Crew sync completed.",
    "summary": {
        "updated": 5,
        "skipped": 1,
        "errors": 0
    },
    "details": {
        "updated": ["juan.dela.cruz@example.com"],
        "skipped": [
            {
                "email": "unknown@example.com",
                "reason": "No crew member found with this email."
            }
        ],
        "errors": []
    }
}
```

#### Error Response (422)

```json
{
    "success": false,
    "message": "Validation failed.",
    "errors": {
        "crew.0.profile.first_name": [
            "The crew.0.profile.first_name field is required when crew.0.profile is present."
        ]
    }
}
```

---

### 3.2 Contracts Sync

**`POST /api/mpip/contracts/sync`**

Receives contract data from MPIP and upserts records in the Super App. The upsert key is `contract_number`. Matching crew is found by `email`.

#### Request Body

```json
{
    "contracts": [
        {
            "contract_number": "NYK-2026-001",
            "email": "juan.dela.cruz@example.com",
            "vessel_name": "MV Horizon",
            "rank_code": "MAS",
            "port_of_departure": "Manila",
            "port_of_arrival": "Rotterdam",
            "contract_start_date": "2026-01-01",
            "contract_end_date": "2026-07-01",
            "duration_months": 6,
            "departure_date": "2026-01-15",
            "arrival_date": "2026-01-20",
            "basic_wage": 3000.0,
            "fixed_overtime": 750.0,
            "leave_pay": 450.0,
            "subsistence_allowance": 300.0,
            "vacation_leave_conversion": 200.0,
            "total_guaranteed_monthly": 4700.0,
            "currency": "USD",
            "contract_status": "active",
            "remarks": null
        }
    ]
}
```

#### Request Fields

| Field                     | Type    | Required | Description                                              |
| ------------------------- | ------- | -------- | -------------------------------------------------------- |
| contracts                 | array   | Yes      | Array of contract objects (min 1)                        |
| contract_number           | string  | Yes      | Unique contract reference (upsert key)                   |
| email                     | string  | Yes      | Crew member email (lookup key for user_id)               |
| vessel_name               | string  | No       | Vessel name matching `vessels.name` in the Super App     |
| rank_code                 | string  | No       | Rank code matching `ranks.code`                          |
| port_of_departure         | string  | No       | Departure port                                           |
| port_of_arrival           | string  | No       | Arrival port                                             |
| contract_start_date       | date    | Yes      | Contract start date (YYYY-MM-DD)                         |
| contract_end_date         | date    | No       | Contract end date (auto-calculated if duration provided) |
| duration_months           | integer | No       | Contract duration in months                              |
| departure_date            | date    | No       | Actual departure date                                    |
| arrival_date              | date    | No       | Actual arrival date                                      |
| basic_wage                | decimal | No       | Basic monthly wage                                       |
| fixed_overtime            | decimal | No       | Fixed overtime pay                                       |
| leave_pay                 | decimal | No       | Leave pay                                                |
| subsistence_allowance     | decimal | No       | Subsistence allowance                                    |
| vacation_leave_conversion | decimal | No       | Vacation leave conversion                                |
| total_guaranteed_monthly  | decimal | No       | Total guaranteed monthly compensation                    |
| currency                  | string  | No       | Currency code (default: USD)                             |
| contract_status           | string  | No       | MPIP contract status (active / completed / cancelled)    |
| remarks                   | string  | No       | Free-form remarks                                        |

#### Success Response (200)

```json
{
    "success": true,
    "message": "Contracts sync completed.",
    "summary": {
        "upserted": 3,
        "skipped": 1,
        "errors": 0
    },
    "details": {
        "upserted": ["NYK-2026-001", "NYK-2026-002", "NYK-2026-003"],
        "skipped": [
            {
                "contract_number": "NYK-2026-004",
                "reason": "No crew member found with email: unknown@example.com"
            }
        ],
        "errors": []
    }
}
```

---

### 3.3 Wage Scale Sync

**`POST /api/mpip/wages/sync`**

Receives the wage scale table from MPIP and upserts records in the Super App. The upsert key is `(rank_id, vessel_type_id, effective_date)`. Rank is resolved by `rank_code`; vessel type is resolved by `vessel_type_name`.

#### Request Body

```json
{
    "wages": [
        {
            "rank_code": "MAS",
            "vessel_type_name": "Bulk Carrier",
            "effective_date": "2026-01-01",
            "basic_wage": 3000.0,
            "fixed_overtime": 750.0,
            "leave_pay": 450.0,
            "subsistence_allowance": 300.0,
            "vacation_leave_conversion": 200.0,
            "total_guaranteed_monthly": 4700.0,
            "currency": "USD"
        }
    ]
}
```

#### Request Fields

| Field                     | Type    | Required | Description                                                |
| ------------------------- | ------- | -------- | ---------------------------------------------------------- |
| wages                     | array   | Yes      | Array of wage scale objects (min 1)                        |
| rank_code                 | string  | No       | Rank code matching `ranks.code` (NULL = all ranks)         |
| vessel_type_name          | string  | No       | Vessel type name matching `vessel_types.name` (NULL = all) |
| effective_date            | date    | Yes      | Effective date of this wage scale (YYYY-MM-DD)             |
| basic_wage                | decimal | Yes      | Basic monthly wage                                         |
| fixed_overtime            | decimal | No       | Fixed overtime pay (default: 0)                            |
| leave_pay                 | decimal | No       | Leave pay (default: 0)                                     |
| subsistence_allowance     | decimal | No       | Subsistence allowance (default: 0)                         |
| vacation_leave_conversion | decimal | No       | Vacation leave conversion (default: 0)                     |
| total_guaranteed_monthly  | decimal | Yes      | Total guaranteed monthly compensation                      |
| currency                  | string  | No       | Currency code (default: USD)                               |

#### Success Response (200)

```json
{
    "success": true,
    "message": "Wage scale sync completed.",
    "summary": {
        "upserted": 10,
        "errors": 0
    },
    "details": {
        "errors": []
    }
}
```

---

## Part 4: Error Handling

### Authentication Failure (401)

```json
{
    "success": false,
    "message": "Unauthorized. Invalid or missing MPIP shared secret."
}
```

### Service Unavailable — Missing Config (503)

```json
{
    "success": false,
    "message": "MPIP integration is not configured on this server."
}
```

### Validation Error (422)

```json
{
    "success": false,
    "message": "Validation failed.",
    "errors": {
        "contracts.0.contract_start_date": [
            "The contracts.0.contract_start_date field is required."
        ]
    }
}
```

---

## Part 5: `modified_by` Convention

All records created or updated via MPIP endpoints have `modified_by = 'MPIP API'`. This distinguishes MPIP-sourced changes from admin edits (where `modified_by` is the admin's full name).

> **Note:** The `HasModifiedBy` trait only fires when a user is authenticated via Sanctum. Since MPIP webhooks use shared-secret auth (not Sanctum), `modified_by` must be set explicitly to `'MPIP API'` in all MPIP controllers.

---

## Part 6: Environment Configuration

Add the following to your `.env` file:

```env
# MPIP Integration
MPIP_SHARED_SECRET=your-strong-random-secret-here
```

Generate a secure secret:

```bash
php artisan key:generate --show | head -c 64
# or
openssl rand -hex 32
```

---

## Part 7: Lookup Matching

MPIP sends human-readable codes/names. The Super App resolves them to database IDs:

| MPIP Field         | Resolved via        | Super App field  |
| ------------------ | ------------------- | ---------------- |
| `rank_code`        | `ranks.code`        | `rank_id`        |
| `vessel_name`      | `vessels.name`      | `vessel_id`      |
| `vessel_type_name` | `vessel_types.name` | `vessel_type_id` |
| `email`            | `users.email`       | `user_id`        |

Unresolved lookups result in `NULL` for that FK (e.g. unknown `rank_code` → `rank_id = NULL`) and the record is still saved. Unresolved `email` causes the record to be **skipped** (no user to assign it to).
