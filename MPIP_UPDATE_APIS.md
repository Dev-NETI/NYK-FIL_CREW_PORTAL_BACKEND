# MPIP Inbound Update APIs

**Controllers:** `app/Http/Controllers/Api/Mpip/MpipCrewUpdateController.php`, `MpipDocumentUpdateController.php`
**Routes registered in:** `routes/api.php` under `mpip.auth` middleware
**Last updated:** 2026-04-06

---

## Overview

| #   | Method | Endpoint                                     | Purpose                                    |
| --- | ------ | -------------------------------------------- | ------------------------------------------ |
| 1   | `PUT`  | `/api/mpip/update/cruise/{crew_id}`          | Upsert cruise crew (is_industrial = 0)     |
| 2   | `PUT`  | `/api/mpip/update/non-nyk/{crew_id}`         | Upsert non-NYK crew (company_id ≠ 1)       |
| 3   | `PUT`  | `/api/mpip/update/industrial/{crew_id}`      | Upsert industrial crew (is_industrial = 1) |
| 4   | `PUT`  | `/api/mpip/documents/travel/{crew_id}`       | Upsert travel documents                    |
| 5   | `PUT`  | `/api/mpip/documents/employment/{crew_id}`   | Upsert employment documents                |
| 6   | `PUT`  | `/api/mpip/documents/certificates/{crew_id}` | Upsert certificates                        |

**Base URL:** `http://<host>/api`

**Key behaviors:**

- **Create or update** — if no crew record exists for `crew_id`, endpoints 1–3 create a new one (requires `basic_info.email`). Document endpoints (4–6) return `404` if the crew member doesn't exist yet.
- **Partial update** — only fields present in the payload are written. Omitted fields are never touched.
- **Section-level gating** — if an entire section (e.g. `employment`) is absent from the payload, that section is skipped entirely.
- **Atomic** — each request runs inside a single DB transaction.

---

## Authentication

All requests must include:

```
Authorization: Bearer <MPIP_SHARED_SECRET>
Content-Type: application/json
```

The secret is configured in `.env`:

```
MPIP_SHARED_SECRET=your-secret-here
```

**401 response** when the token is missing or incorrect:

```json
{
    "message": "Unauthorized."
}
```

---

## Endpoints 1–3: Crew Upsert

### 1. PUT `/api/mpip/update/cruise/{crew_id}`

Applies to crew with `users.is_industrial = 0`. On update, returns `422` if the matched record is industrial.

### 2. PUT `/api/mpip/update/non-nyk/{crew_id}`

Applies to crew with `user_profiles.company_id ≠ 1`. On update, returns `422` if the matched record belongs to NYK-Fil (company_id = 1).

### 3. PUT `/api/mpip/update/industrial/{crew_id}`

Applies to crew with `users.is_industrial = 1`. On update, returns `422` if the matched record is not industrial.

### Create vs Update behavior

| Scenario                                          | Behavior                                                    |
| ------------------------------------------------- | ----------------------------------------------------------- |
| `crew_id` not found + `basic_info.email` provided | Creates `User` + `UserProfile`, then processes all sections |
| `crew_id` not found + no email                    | `422` — email required to create                            |
| `crew_id` not found + email already taken         | `422` — duplicate email                                     |
| `crew_id` found + correct endpoint                | Updates the existing record                                 |
| `crew_id` found + wrong endpoint                  | `422` — crew type mismatch                                  |

On **create**, `is_industrial` is set automatically by the endpoint:

- Cruise → `0`
- Industrial → `1`
- Non-NYK → from `basic_info.is_industrial` (defaults to `0`)

### Payload — Sections T1–T7, T12

All top-level keys are optional. Omit any section you don't want to update.

```json
{
  "basic_info": { ... },
  "contact": { ... },
  "addresses": [ ... ],
  "employment": { ... },
  "education": [ ... ],
  "physical_traits": { ... },
  "allotees": [ ... ],
  "programs": [ ... ]
}
```

#### T1 — `basic_info`

| Field           | Type                                              | Notes                                                             |
| --------------- | ------------------------------------------------- | ----------------------------------------------------------------- |
| `email`         | string                                            | **Required on create only**                                       |
| `first_name`    | string                                            | → `user_profiles.first_name`                                      |
| `middle_name`   | string                                            | → `user_profiles.middle_name`                                     |
| `last_name`     | string                                            | → `user_profiles.last_name`                                       |
| `suffix`        | string                                            | → `user_profiles.suffix`                                          |
| `birth_date`    | date                                              | → `user_profiles.birth_date`                                      |
| `birth_place`   | string                                            | → `user_profiles.birth_place`                                     |
| `gender`        | `male` \| `female`                                | → `user_profiles.gender`                                          |
| `nationality`   | string                                            | → `user_profiles.nationality`                                     |
| `civil_status`  | `single` \| `married` \| `widowed` \| `separated` | → `user_profiles.civil_status`                                    |
| `religion`      | string                                            | → `user_profiles.religion`                                        |
| `blood_type`    | string                                            | → `user_profiles.blood_type`                                      |
| `rank_code`     | string                                            | Resolved → `user_profiles.rank_id`                                |
| `fleet_name`    | string                                            | Resolved → `user_profiles.fleet_id`                               |
| `company_name`  | string                                            | Resolved → `user_profiles.company_id`                             |
| `is_industrial` | boolean                                           | → `users.is_industrial` (ignored for cruise/industrial on create) |

#### T2 — `contact`

| Field                            | Type   | DB Column                                      |
| -------------------------------- | ------ | ---------------------------------------------- |
| `mobile_number`                  | string | `user_contacts.mobile_number`                  |
| `alternate_phone`                | string | `user_contacts.alternate_phone`                |
| `emergency_contact_name`         | string | `user_contacts.emergency_contact_name`         |
| `emergency_contact_phone`        | string | `user_contacts.emergency_contact_phone`        |
| `emergency_contact_relationship` | string | `user_contacts.emergency_contact_relationship` |

#### T3 — `addresses` (array)

Each item:

| Field            | Type                     | Notes                  |
| ---------------- | ------------------------ | ---------------------- |
| `type`           | `permanent` \| `current` | **Required per item**  |
| `full_address`   | string                   | Free-text full address |
| `street_address` | string                   | Street / unit          |
| `barangay`       | string                   | Barangay               |
| `zip_code`       | string                   | ZIP                    |

#### T4 — `employment`

| Field                | Type    | Notes                                                          |
| -------------------- | ------- | -------------------------------------------------------------- |
| `rank_code`          | string  | Resolved → `user_employment.rank_id`                           |
| `fleet_name`         | string  | Resolved → `user_employment.fleet_id`                          |
| `crew_status`        | enum    | `on_board`, `on_vacation`, `standby`, `resigned`, `terminated` |
| `hire_status`        | enum    | `new_hire`, `re_hire`, `promoted`, `transferred`               |
| `hire_date`          | date    |                                                                |
| `passport_number`    | string  |                                                                |
| `passport_expiry`    | date    |                                                                |
| `seaman_book_number` | string  |                                                                |
| `seaman_book_expiry` | date    |                                                                |
| `basic_salary`       | numeric |                                                                |
| `employment_notes`   | string  |                                                                |

#### T5 — `education` (array)

| Field             | Type   | Notes                                                                         |
| ----------------- | ------ | ----------------------------------------------------------------------------- |
| `school_name`     | string | **Required per item** — upsert key (+ `degree`)                               |
| `degree`          | string |                                                                               |
| `education_level` | enum   | `high_school`, `college`, `vocational`, `post_graduate`, `higher_educational` |
| `date_graduated`  | date   |                                                                               |

#### T6 — `physical_traits`

| Field        | Type    | DB Column                         |
| ------------ | ------- | --------------------------------- |
| `height_cm`  | numeric | `user_physical_traits.height`     |
| `weight_kg`  | numeric | `user_physical_traits.weight`     |
| `blood_type` | string  | `user_physical_traits.blood_type` |
| `eye_color`  | string  | `user_physical_traits.eye_color`  |
| `hair_color` | string  | `user_physical_traits.hair_color` |

#### T7 — `allotees` (array)

| Field                  | Type               | Notes                                     |
| ---------------------- | ------------------ | ----------------------------------------- |
| `name`                 | string             | **Required per item**                     |
| `relationship`         | string             | **Required per item**                     |
| `mobile_number`        | string             |                                           |
| `email`                | string             | Used as upsert key if provided            |
| `date_of_birth`        | date               |                                           |
| `gender`               | `male` \| `female` |                                           |
| `is_primary`           | boolean            | Sets `user_employment.primary_allotee_id` |
| `is_emergency_contact` | boolean            |                                           |

#### T12 — `programs` (array)

| Field          | Type   | Notes                                              |
| -------------- | ------ | -------------------------------------------------- |
| `program_name` | string | **Required per item** — matched to `programs.name` |
| `batch`        | string |                                                    |

### Response — Success (200)

```json
{
    "success": true,
    "action": "created",
    "message": "Crew record created successfully.",
    "crew_id": "CR-2024-001",
    "updated_sections": ["basic_info", "employment", "programs"],
    "timestamp": "2026-04-06T10:00:00+08:00"
}
```

`action` is `"created"` or `"updated"`.

---

## Endpoints 4–6: Document Upsert

These endpoints accept the `crew_id` of an **existing** crew member. No category condition check is applied — any crew member can have their documents updated regardless of type.

### 4. PUT `/api/mpip/documents/travel/{crew_id}`

#### Payload

```json
{
    "travel_documents": [
        {
            "document_type": "Passport",
            "id_no": "P1234567A",
            "place_of_issue": "Manila",
            "date_of_issue": "2020-01-15",
            "expiration_date": "2030-01-14",
            "remaining_pages": 20,
            "is_us_visa": false,
            "visa_type": null
        }
    ]
}
```

| Field             | Type    | Notes                                                  |
| ----------------- | ------- | ------------------------------------------------------ |
| `document_type`   | string  | **Required** — matched to `travel_document_types.name` |
| `id_no`           | string  | **Required** — upsert key (with document_type)         |
| `place_of_issue`  | string  |                                                        |
| `date_of_issue`   | date    |                                                        |
| `expiration_date` | date    |                                                        |
| `remaining_pages` | integer |                                                        |
| `is_us_visa`      | boolean |                                                        |
| `visa_type`       | string  |                                                        |

Records with unknown `document_type` are skipped (listed in `details.skipped`).

### 5. PUT `/api/mpip/documents/employment/{crew_id}`

#### Payload

```json
{
    "employment_documents": [
        {
            "document_type": "SIRB",
            "document_number": "SIRB-2024-00123"
        }
    ]
}
```

| Field             | Type   | Notes                                                      |
| ----------------- | ------ | ---------------------------------------------------------- |
| `document_type`   | string | **Required** — matched to `employment_document_types.name` |
| `document_number` | string | Upsert key (with document_type)                            |

### 6. PUT `/api/mpip/documents/certificates/{crew_id}`

#### Payload

```json
{
    "certificates": [
        {
            "certificate_type": "STCW",
            "certificate_name": "Basic Safety Training",
            "certificate_no": "BST-2024-001",
            "issued_by": "MAAP",
            "date_issued": "2024-03-01",
            "expiry_date": "2029-03-01",
            "grade": "Passed",
            "rank_permitted": "All Ranks"
        }
    ]
}
```

| Field              | Type   | Notes                                                      |
| ------------------ | ------ | ---------------------------------------------------------- |
| `certificate_type` | string | Used to auto-create certificate master record if not found |
| `certificate_name` | string | **Required** — matched to `certificates.name`              |
| `certificate_no`   | string | Upsert key (with certificate_name)                         |
| `issued_by`        | string |                                                            |
| `date_issued`      | date   |                                                            |
| `expiry_date`      | date   |                                                            |
| `grade`            | string |                                                            |
| `rank_permitted`   | string |                                                            |

If `certificate_name` is not found in the `certificates` table:

- If `certificate_type` is also provided and matches a `certificate_types.name`, a new master `Certificate` record is auto-created.
- Otherwise the item is skipped (listed in `details.skipped`).

### Response — Document Endpoints (200)

```json
{
    "success": true,
    "message": "Travel documents sync completed.",
    "crew_id": "CR-2024-001",
    "summary": {
        "upserted": 2,
        "skipped": 0
    },
    "details": {
        "upserted": ["Passport / P1234567A", "Seaman's Book / SB-987654"],
        "skipped": []
    },
    "timestamp": "2026-04-06T10:00:00+08:00"
}
```

---

## Common Error Responses

| Status | Scenario                                                                 |
| ------ | ------------------------------------------------------------------------ |
| `401`  | Missing or invalid Bearer token                                          |
| `400`  | Validation error (invalid field values)                                  |
| `404`  | `crew_id` not found (document endpoints)                                 |
| `422`  | Wrong endpoint for crew type / missing email on create / duplicate email |

---

## Validation Rules

### Endpoints 1–3: Crew Upsert

All sections are optional at the top level. Within each section, rules apply per field.

#### T1 — `basic_info`

| Field | Rule |
|-------|------|
| `basic_info` | `nullable\|array` |
| `basic_info.email` | `required\|email` _(create only — not accepted on update)_ |
| `basic_info.first_name` | `nullable\|string\|max:255` |
| `basic_info.middle_name` | `nullable\|string\|max:255` |
| `basic_info.last_name` | `nullable\|string\|max:255` |
| `basic_info.suffix` | `nullable\|string\|max:50` |
| `basic_info.birth_date` | `nullable\|date` |
| `basic_info.birth_place` | `nullable\|string\|max:255` |
| `basic_info.gender` | `nullable\|string\|in:male,female` |
| `basic_info.nationality` | `nullable\|string\|max:100` |
| `basic_info.civil_status` | `nullable\|string\|in:single,married,widowed,separated` |
| `basic_info.religion` | `nullable\|string\|max:100` |
| `basic_info.blood_type` | `nullable\|string\|max:10` |
| `basic_info.rank_code` | `nullable\|string\|max:50` |
| `basic_info.fleet_name` | `nullable\|string\|max:255` |
| `basic_info.company_name` | `nullable\|string\|max:255` |
| `basic_info.is_industrial` | `nullable\|boolean` |

#### T2 — `contact`

| Field | Rule |
|-------|------|
| `contact` | `nullable\|array` |
| `contact.mobile_number` | `nullable\|string\|max:50` |
| `contact.alternate_phone` | `nullable\|string\|max:50` |
| `contact.emergency_contact_name` | `nullable\|string\|max:255` |
| `contact.emergency_contact_phone` | `nullable\|string\|max:50` |
| `contact.emergency_contact_relationship` | `nullable\|string\|max:100` |

#### T3 — `addresses`

| Field | Rule |
|-------|------|
| `addresses` | `nullable\|array` |
| `addresses.*.type` | `required_with:addresses\|string\|in:permanent,current` |
| `addresses.*.street_address` | `nullable\|string\|max:255` |
| `addresses.*.barangay` | `nullable\|string\|max:255` |
| `addresses.*.city` | `nullable\|string\|max:255` |
| `addresses.*.province` | `nullable\|string\|max:255` |
| `addresses.*.region` | `nullable\|string\|max:255` |
| `addresses.*.zip_code` | `nullable\|string\|max:20` |
| `addresses.*.full_address` | `nullable\|string\|max:500` |

#### T4 — `employment`

| Field | Rule |
|-------|------|
| `employment` | `nullable\|array` |
| `employment.rank_code` | `nullable\|string\|max:50` |
| `employment.fleet_name` | `nullable\|string\|max:255` |
| `employment.crew_status` | `nullable\|string\|in:on_board,on_vacation,standby,resigned,terminated` |
| `employment.hire_status` | `nullable\|string\|in:new_hire,re_hire,promoted,transferred` |
| `employment.hire_date` | `nullable\|date` |
| `employment.passport_number` | `nullable\|string\|max:100` |
| `employment.passport_expiry` | `nullable\|date` |
| `employment.seaman_book_number` | `nullable\|string\|max:100` |
| `employment.seaman_book_expiry` | `nullable\|date` |
| `employment.basic_salary` | `nullable\|numeric\|min:0` |
| `employment.employment_notes` | `nullable\|string` |

#### T5 — `education`

| Field | Rule |
|-------|------|
| `education` | `nullable\|array` |
| `education.*.school_name` | `required_with:education\|string\|max:255` |
| `education.*.degree` | `nullable\|string\|max:255` |
| `education.*.education_level` | `nullable\|string\|in:high_school,college,vocational,post_graduate,higher_educational` |
| `education.*.date_graduated` | `nullable\|date` |

#### T6 — `physical_traits`

| Field | Rule |
|-------|------|
| `physical_traits` | `nullable\|array` |
| `physical_traits.height_cm` | `nullable\|numeric\|min:0` |
| `physical_traits.weight_kg` | `nullable\|numeric\|min:0` |
| `physical_traits.blood_type` | `nullable\|string\|max:10` |
| `physical_traits.eye_color` | `nullable\|string\|max:50` |
| `physical_traits.hair_color` | `nullable\|string\|max:50` |

#### T7 — `allotees`

| Field | Rule |
|-------|------|
| `allotees` | `nullable\|array` |
| `allotees.*.name` | `required_with:allotees\|string\|max:255` |
| `allotees.*.relationship` | `required_with:allotees\|string\|max:100` |
| `allotees.*.mobile_number` | `nullable\|string\|max:50` |
| `allotees.*.email` | `nullable\|email` |
| `allotees.*.date_of_birth` | `nullable\|date` |
| `allotees.*.gender` | `nullable\|string\|in:male,female` |
| `allotees.*.is_primary` | `nullable\|boolean` |
| `allotees.*.is_emergency_contact` | `nullable\|boolean` |

#### T12 — `programs`

| Field | Rule |
|-------|------|
| `programs` | `nullable\|array` |
| `programs.*.program_name` | `required_with:programs\|string\|max:255` |
| `programs.*.batch` | `nullable\|string\|max:100` |

---

### Endpoint 4: Travel Documents

| Field | Rule |
|-------|------|
| `travel_documents` | `required\|array\|min:1` |
| `travel_documents.*.document_type` | `required\|string\|max:255` |
| `travel_documents.*.id_no` | `required\|string\|max:100` |
| `travel_documents.*.place_of_issue` | `nullable\|string\|max:255` |
| `travel_documents.*.date_of_issue` | `nullable\|date` |
| `travel_documents.*.expiration_date` | `nullable\|date` |
| `travel_documents.*.remaining_pages` | `nullable\|integer` |
| `travel_documents.*.is_us_visa` | `nullable\|boolean` |
| `travel_documents.*.visa_type` | `nullable\|string\|max:100` |

### Endpoint 5: Employment Documents

| Field | Rule |
|-------|------|
| `employment_documents` | `required\|array\|min:1` |
| `employment_documents.*.document_type` | `required\|string\|max:255` |
| `employment_documents.*.document_number` | `nullable\|string\|max:100` |

### Endpoint 6: Certificates

| Field | Rule |
|-------|------|
| `certificates` | `required\|array\|min:1` |
| `certificates.*.certificate_type` | `nullable\|string\|max:255` |
| `certificates.*.certificate_name` | `required\|string\|max:255` |
| `certificates.*.certificate_no` | `nullable\|string\|max:100` |
| `certificates.*.issued_by` | `nullable\|string\|max:255` |
| `certificates.*.date_issued` | `nullable\|date` |
| `certificates.*.expiry_date` | `nullable\|date` |
| `certificates.*.grade` | `nullable\|string\|max:100` |
| `certificates.*.rank_permitted` | `nullable\|string\|max:100` |

> **400 Bad Request** is returned when any rule fails. The response body contains an `errors` object keyed by field path:
> ```json
> {
>   "success": false,
>   "message": "Validation failed.",
>   "errors": {
>     "basic_info.gender": ["The basic_info.gender field must be one of: male, female."],
>     "contracts.0.contract_number": ["The contracts.0.contract_number field is required."]
>   }
> }
> ```

---

## Reference Tables

Use values **exactly** as shown — they are matched case-sensitively against the database.

### Companies (`basic_info.company_name`)

> Non-NYK endpoint (`/update/non-nyk/`) rejects company_id = 1 on update.

| company_name (exact) | company_id | Notes |
| -------------------- | ---------- | ----- |
| NYK-Fil Ship Management, Inc. | 1 | NYK main company — **not valid** for non-nyk endpoint |
| NYK-Fil Maritime E-Training, Inc. | 2 | |
| NYK Bulk & Projects Carriers Ltd. | 3 | |
| NYK Line (Asia) Pte. Ltd. | 4 | |
| NYK Shipmanagement Pte. Ltd. | 5 | |
| Yusen Logistics Co., Ltd. | 6 | |
| MTI Co., Ltd. | 7 | |
| NYK Cool AB | 8 | |

### Ranks (`rank_code`)

Used in `basic_info.rank_code`, `employment.rank_code`, `contracts.*.rank_code`.

| rank_code | Rank Name | Department |
| --------- | --------- | ---------- |
| `MSTR` | Master | Deck |
| `CM` | Chief Mate | Deck |
| `CM-2` | Junior Chief Mate | Deck |
| `2M` | Second Mate | Deck |
| `3M` | Third Mate | Deck |
| `JR3M` | Junior Third Mate | Deck |
| `D/CDT` | Deck Cadet | Deck |
| `DMA` | Deck Maintenance Assistant | Deck |
| `BSN` | Boatswain | Deck |
| `PMN` | Pumpman | Deck |
| `AB` | Able Bodies Seaman | Deck |
| `OS` | Ordinary Seaman | Deck |
| `DBOY` | Deck Boy | Deck |
| `CE` | Chief Engineer | Engine |
| `1AE` | First Assistant Engineer | Engine |
| `1AE-2` | Junior First Assistant Engineer | Engine |
| `2AE` | Second Assistant Engineer | Engine |
| `3AE` | Third Assistant Engineer | Engine |
| `E/E` | Electrical Engineer | Engine |
| `JR3AE` | Junior Third Assistant Engineer | Engine |
| `E/CDT` | Engine Cadet | Engine |
| `EMA` | Engine Maintenance Assistant | Engine |
| `FTR` | Fitter | Engine |
| `FMA` | Fitter Maintenance Assistant | Engine |
| `OLR` | Oiler | Engine |
| `WPR` | Wiper | Engine |
| `EBOY` | Engine Boy | Engine |
| `ELECT` | Electrician | Engine |
| `A/ELECT` | Assistant Electrician | Engine |
| `H/E` | Helper Electrician | Engine |
| `CCK` | Chief Cook | Catering |
| `2CK` | Second Cook | Catering |
| `MSM` | Messman | Catering |
| `CBOY` | Catering Boy | Catering |

### Fleets (`fleet_name`)

Used in `basic_info.fleet_name` and `employment.fleet_name`.

| fleet_name (exact) |
| ------------------ |
| FLEET A |
| FLEET B1 |
| FLEET B2 |
| FLEET C1 |
| FLEET C2 |
| FLEET D1 |
| FLEET D2 |
| FLEET E1 |
| FLEET E2 |
| NTMA FLEET |

### Travel Document Types (`document_type`)

Used in `PUT /api/mpip/documents/travel/{crew_id}`.

| document_type (exact) |
| --------------------- |
| Passport |
| Seafarer's Identification and Record Book (SIRB) |
| Seafarer's Identity Document (SID) |
| US VISA |

### Employment Document Types (`document_type`)

Used in `PUT /api/mpip/documents/employment/{crew_id}`.

| document_type (exact) |
| --------------------- |
| TIN |
| SSS |
| PAG-IBIG |
| PHILHEALTH |
| SRN |
| DMW e-REG |
| MARCOPAY |

### Certificate Types (`certificate_type`)

Used in `PUT /api/mpip/documents/certificates/{crew_id}` to auto-create unknown certificates.

| certificate_type (exact) |
| ------------------------ |
| STCW Certificates |
| Government Required |
| NMC Training Certificate |
| TESDA Certificate |
| Other Training Certificate |
| JISS Certificate |

### Programs (`program_name`)

Used in `programs.*.program_name`.

| program_name (exact) |
| -------------------- |
| NTMA Cadetship |
| NYK-PANAMA Cadetship |
| OJT Program |
| Maritime Ratings Program (MRP) |
| ETO Development Program (EDP) |

---

## Sample Payloads

### Cruise crew — Full create

```json
PUT /api/mpip/update/cruise/CR-2026-999

{
  "basic_info": {
    "email": "juan.dela.cruz@example.com",
    "first_name": "Juan",
    "middle_name": "Santos",
    "last_name": "Dela Cruz",
    "birth_date": "1990-05-15",
    "gender": "male",
    "nationality": "Filipino",
    "civil_status": "married",
    "rank_code": "AB",
    "company_name": "NYK-Fil Ship Management, Inc."
  },
  "contact": {
    "mobile_number": "+639171234567",
    "emergency_contact_name": "Maria Dela Cruz",
    "emergency_contact_phone": "+639189876543",
    "emergency_contact_relationship": "Spouse"
  },
  "addresses": [
    {
      "type": "permanent",
      "street_address": "123 Rizal St.",
      "barangay": "Poblacion",
      "zip_code": "1000"
    }
  ],
  "employment": {
    "crew_status": "on_board",
    "hire_status": "new_hire",
    "hire_date": "2024-01-10",
    "seaman_book_number": "SB-2024-00456",
    "seaman_book_expiry": "2029-01-10",
    "basic_salary": 1500.00
  },
  "physical_traits": {
    "height_cm": 170.5,
    "weight_kg": 68.0,
    "blood_type": "O+",
    "eye_color": "Brown",
    "hair_color": "Black"
  },
  "programs": [
    { "program_name": "Safety Awareness Program", "batch": "2024-A" }
  ]
}
```

### Non-NYK crew — Partial update (employment only)

```json
PUT /api/mpip/update/non-nyk/CR-2025-042

{
  "employment": {
    "crew_status": "on_vacation",
    "hire_status": "re_hire"
  }
}
```

### Industrial crew — Full create

```json
PUT /api/mpip/update/industrial/IND-2026-001

{
  "basic_info": {
    "email": "pedro.reyes@industrialcorp.com",
    "first_name": "Pedro",
    "last_name": "Reyes",
    "gender": "male",
    "nationality": "Filipino",
    "rank_code": "C/E",
    "fleet_name": "Industrial Fleet A"
  },
  "employment": {
    "crew_status": "on_board",
    "hire_status": "new_hire",
    "hire_date": "2026-03-01"
  }
}
```

### Travel documents

```json
PUT /api/mpip/documents/travel/CR-2024-001

{
  "travel_documents": [
    {
      "document_type": "Passport",
      "id_no": "P1234567A",
      "place_of_issue": "DFA Manila",
      "date_of_issue": "2020-01-15",
      "expiration_date": "2030-01-14",
      "remaining_pages": 20
    },
    {
      "document_type": "US Visa",
      "id_no": "USVISA-XYZ-789",
      "expiration_date": "2027-06-30",
      "is_us_visa": true,
      "visa_type": "C1/D"
    }
  ]
}
```

### Employment documents

```json
PUT /api/mpip/documents/employment/CR-2024-001

{
  "employment_documents": [
    {
      "document_type": "SIRB",
      "document_number": "SIRB-2024-00123"
    },
    {
      "document_type": "Medical Certificate"
    }
  ]
}
```

### Certificates

```json
PUT /api/mpip/documents/certificates/CR-2024-001

{
  "certificates": [
    {
      "certificate_type": "STCW",
      "certificate_name": "Basic Safety Training",
      "certificate_no": "BST-2024-001",
      "issued_by": "MAAP",
      "date_issued": "2024-03-01",
      "expiry_date": "2029-03-01",
      "grade": "Passed"
    },
    {
      "certificate_type": "STCW",
      "certificate_name": "GMDSS",
      "certificate_no": "GMDSS-2024-055",
      "date_issued": "2024-06-15",
      "expiry_date": "2029-06-15"
    }
  ]
}
```

---

## Postman Testing Guide

### Environment Setup

Create a Postman environment with:

| Variable     | Value                                          |
| ------------ | ---------------------------------------------- |
| `base_url`   | `http://localhost:8000/api`                    |
| `mpip_token` | _(value of `MPIP_SHARED_SECRET` from `.env`)_  |
| `crew_id`    | _(a crew_id to test with, e.g. `CR-2024-001`)_ |

### Headers (all requests)

```
Authorization: Bearer {{mpip_token}}
Content-Type: application/json
```

### Test Cases

| #   | Request                                                                                 | Expected                  |
| --- | --------------------------------------------------------------------------------------- | ------------------------- |
| 1   | `PUT {{base_url}}/mpip/update/cruise/NEW-001` — with `basic_info.email`                 | `200` `action: "created"` |
| 2   | `PUT {{base_url}}/mpip/update/cruise/NEW-001` — same crew_id again                      | `200` `action: "updated"` |
| 3   | `PUT {{base_url}}/mpip/update/cruise/NEW-NEW` — no email                                | `422` missing email       |
| 4   | `PUT {{base_url}}/mpip/update/industrial/{{crew_id}}` — where crew is cruise            | `422` crew type mismatch  |
| 5   | `PUT {{base_url}}/mpip/update/cruise/ANOTHER-NEW` — with email already taken            | `422` duplicate email     |
| 6   | No `Authorization` header                                                               | `401`                     |
| 7   | `PUT {{base_url}}/mpip/documents/travel/UNKNOWN-ID`                                     | `404` not found           |
| 8   | `PUT {{base_url}}/mpip/documents/certificates/{{crew_id}}` — unknown cert name, no type | Skipped in response       |

### SQL Verification

```sql
-- Verify crew was created
SELECT u.id, u.email, u.is_crew, u.is_industrial, p.crew_id
FROM users u JOIN user_profiles p ON p.user_id = u.id
WHERE p.crew_id = 'CR-2024-001';

-- Check all sections updated
SELECT * FROM user_profiles WHERE crew_id = 'CR-2024-001';
SELECT * FROM user_contacts WHERE user_id = (SELECT user_id FROM user_profiles WHERE crew_id = 'CR-2024-001');
SELECT * FROM user_employment WHERE user_id = (SELECT user_id FROM user_profiles WHERE crew_id = 'CR-2024-001');
SELECT * FROM user_physical_traits WHERE user_id = (SELECT user_id FROM user_profiles WHERE crew_id = 'CR-2024-001');
SELECT * FROM contracts WHERE user_id = (SELECT user_id FROM user_profiles WHERE crew_id = 'CR-2024-001');

-- Verify documents
SELECT td.*, tdt.name AS doc_type FROM travel_documents td
  JOIN travel_document_types tdt ON tdt.id = td.travel_document_type_id
  WHERE td.crew_id = 'CR-2024-001';

SELECT ed.*, edt.name AS doc_type FROM employment_documents ed
  JOIN employment_document_types edt ON edt.id = ed.employment_document_type_id
  WHERE ed.crew_id = 'CR-2024-001';

SELECT cc.*, c.name AS certificate FROM crew_certificates cc
  JOIN certificates c ON c.id = cc.certificate_id
  WHERE cc.crew_id = 'CR-2024-001';
```
