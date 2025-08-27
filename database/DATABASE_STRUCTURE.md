# Crew Portal Database Structure

## Overview

This document describes the normalized database structure for the Crew Portal Backend. The database is designed to efficiently manage crew information, vessel assignments, geographic data, and related business entities.

## Database Schema Diagram

```
Islands → Regions → Provinces → Cities
                                  ↓
RankCategories → RankGroups → Ranks
                               ↓
VesselTypes → Vessels ← Fleets
                ↓
Schools → Crew ← Addresses ← Allotees
           ↓
        Contracts
```

## Core Entities

### 1. Geographic Hierarchy

**Islands** (`islands`)

-   Primary geographic division (Luzon, Visayas, Mindanao)
-   Fields: `id`, `name`, `code`, `created_at`, `updated_at`

**Regions** (`regions`)

-   Administrative regions within islands
-   Fields: `id`, `island_id`, `name`, `code`, `created_at`, `updated_at`
-   Relationship: `belongsTo(Island)`

**Provinces** (`provinces`)

-   Provinces within regions
-   Fields: `id`, `region_id`, `name`, `code`, `created_at`, `updated_at`
-   Relationship: `belongsTo(Region)`

**Cities** (`cities`)

-   Cities/municipalities within provinces
-   Fields: `id`, `province_id`, `name`, `type`, `zip_code`, `created_at`, `updated_at`
-   Relationship: `belongsTo(Province)`

### 2. Address Management

**Addresses** (`addresses`)

-   Flexible address storage for crew and allotees
-   Fields: `id`, `type`, `street_address`, `barangay`, `city_id`, `zip_code`, `landmark`, `latitude`, `longitude`, `is_active`, `created_at`, `updated_at`
-   Relationship: `belongsTo(City)`
-   Types: 'permanent', 'allotee', 'temporary'

### 3. Educational Information

**Schools** (`schools`)

-   Educational institutions
-   Fields: `id`, `name`, `type`, `address`, `city_id`, `is_active`, `created_at`, `updated_at`
-   Relationship: `belongsTo(City)`

### 4. Rank Hierarchy

**Rank Categories** (`rank_categories`)

-   Top-level rank classifications
-   Fields: `id`, `name`, `description`, `sort_order`, `is_active`, `created_at`, `updated_at`
-   Examples: Officer, Rating, Management

**Rank Groups** (`rank_groups`)

-   Department/specialization groups within categories
-   Fields: `id`, `rank_category_id`, `name`, `description`, `sort_order`, `is_active`, `created_at`, `updated_at`
-   Relationship: `belongsTo(RankCategory)`
-   Examples: Deck, Engine, Catering

**Ranks** (`ranks`)

-   Specific positions within groups
-   Fields: `id`, `rank_group_id`, `name`, `code`, `description`, `hierarchy_level`, `is_officer`, `is_active`, `created_at`, `updated_at`
-   Relationship: `belongsTo(RankGroup)`
-   Examples: Captain, Chief Officer, AB, OS

### 5. Fleet and Vessel Management

**Vessel Types** (`vessel_types`)

-   Classification of vessel types
-   Fields: `id`, `name`, `code`, `description`, `is_active`, `created_at`, `updated_at`
-   Examples: Tanker, Container, Bulk Carrier

**Fleets** (`fleets`)

-   Fleet organization
-   Fields: `id`, `name`, `code`, `description`, `manager_name`, `manager_contact`, `is_active`, `created_at`, `updated_at`

**Vessels** (`vessels`)

-   Individual vessel information
-   Fields: `id`, `name`, `vessel_id`, `vessel_type_id`, `fleet_id`, `flag_state`, `gross_tonnage`, `deadweight_tonnage`, `built_year`, `shipyard`, `status`, `specifications`, `is_active`, `created_at`, `updated_at`
-   Relationships: `belongsTo(VesselType)`, `belongsTo(Fleet)`
-   Status: active, maintenance, drydock, decommissioned

### 6. Beneficiary Management

**Allotees** (`allotees`)

-   Emergency contacts and beneficiaries
-   Fields: `id`, `name`, `relationship`, `mobile_number`, `email`, `address_id`, `date_of_birth`, `gender`, `id_type`, `id_number`, `is_emergency_contact`, `is_beneficiary`, `beneficiary_percentage`, `is_active`, `created_at`, `updated_at`
-   Relationship: `belongsTo(Address)`

### 7. Core Crew Management

**Crew** (`crew`)

-   Main crew member information
-   Fields: `id`, `crew_id`, `name`, `first_name`, `middle_name`, `last_name`, `suffix`, `date_of_birth`, `age`, `gender`, `email`, `mobile_number`, `alternative_mobile`, `permanent_address_id`, `graduated_school_id`, `date_graduated`, `course_degree`, `crew_status`, `hire_status`, `hire_date`, `passport_number`, `passport_expiry`, `seaman_book_number`, `seaman_book_expiry`, `primary_allotee_id`, `user_id`, `is_active`, `created_at`, `updated_at`
-   Relationships:
    -   `belongsTo(Address, 'permanent_address_id')`
    -   `belongsTo(School, 'graduated_school_id')`
    -   `belongsTo(Allotee, 'primary_allotee_id')`
    -   `belongsTo(User)`
    -   `belongsToMany(Allotee)` through pivot table
-   Crew Status: active, on_leave, resigned, terminated, retired
-   Hire Status: hired, candidate, interview, rejected, on_hold

### 8. Contract Management

**Contracts** (`contracts`)

-   Vessel assignment contracts
-   Fields: `id`, `contract_number`, `crew_id`, `vessel_id`, `rank_id`, `departure_date`, `arrival_date`, `duration_months`, `contract_start_date`, `contract_end_date`, `status`, `contract_type`, `basic_salary`, `overtime_rate`, `currency`, `previous_contract_id`, `remarks`, `termination_reason`, `is_active`, `created_at`, `updated_at`
-   Relationships: `belongsTo(Crew)`, `belongsTo(Vessel)`, `belongsTo(Rank)`
-   Status: pending, active, completed, terminated, extended
-   Type: new, extension, promotion, transfer

### 9. Pivot Tables

**Crew Allotees** (`crew_allotees`)

-   Many-to-many relationship between crew and allotees
-   Fields: `id`, `crew_id`, `allotee_id`, `allotment_percentage`, `fixed_amount`, `allotment_type`, `is_primary`, `is_emergency_contact`, `is_active`, `created_at`, `updated_at`

## Key Features

### 1. Normalized Design

-   Eliminates data redundancy
-   Ensures data integrity through foreign key constraints
-   Flexible geographic hierarchy
-   Scalable rank system

### 2. Comprehensive Relationships

-   One-to-Many: Island → Regions → Provinces → Cities
-   Many-to-Many: Crew ↔ Allotees (with detailed pivot data)
-   Hierarchical: Rank Categories → Groups → Ranks
-   Referential: Contracts linking Crew, Vessels, and Ranks

### 3. Audit Trail

-   All tables include `created_at` and `updated_at` timestamps
-   Soft delete capability with `is_active` flags
-   Previous contract references for contract history

### 4. Business Logic Support

-   Auto-calculation of age from date of birth
-   Auto-generation of full names
-   Contract end date calculation
-   Status management for various entities

### 5. Flexible Address System

-   Supports multiple address types
-   Geographic coordinates for mapping
-   Hierarchical location references

### 6. Advanced Querying

-   Scopes for common queries (active, hired, expired)
-   Relationship traversal across the hierarchy
-   Efficient indexing on frequently queried fields

## Usage Examples

### Creating a Crew Member

```php
$crew = Crew::create([
    'crew_id' => 'CR-2024-001',
    'first_name' => 'Juan',
    'last_name' => 'Dela Cruz',
    'date_of_birth' => '1990-01-15',
    'gender' => 'male',
    'email' => 'juan@example.com',
    'mobile_number' => '+639171234567',
    // ... other fields
]);
```

### Assigning to Vessel

```php
$contract = Contract::create([
    'contract_number' => 'CNT-2024-001',
    'crew_id' => $crew->id,
    'vessel_id' => $vessel->id,
    'rank_id' => $rank->id,
    'duration_months' => 12,
    'contract_start_date' => now(),
    'basic_salary' => 2500.00,
    'status' => 'active'
]);
```

### Querying Relationships

```php
// Get all crew on a specific vessel
$vessel->currentCrew();

// Get crew from a specific region
$crew = Crew::whereHas('permanentAddress.city.province.region', function($query) {
    $query->where('name', 'NCR');
});

// Get contracts expiring in 30 days
$expiringContracts = Contract::expiringSoon(30)->get();
```

## Data Integrity Features

### Foreign Key Constraints

-   Cascade deletes where appropriate (crew_allotees)
-   Restrict deletes to prevent orphaned records (geographic data)
-   Set null for optional relationships

### Validation Rules

-   Unique constraints on codes and identifiers
-   Email format validation
-   Date range validations
-   Status enum constraints

### Indexing Strategy

-   Primary keys on all tables
-   Foreign key indexes for efficient joins
-   Composite indexes on frequently queried combinations
-   Unique indexes on business identifiers

## Migration Order

The migrations are designed to run in dependency order:

1. Geographic tables (islands → regions → provinces → cities)
2. Reference tables (schools, rank_categories, rank_groups, ranks)
3. Vessel tables (vessel_types, fleets, vessels)
4. Address and allotee tables
5. Crew table
6. Contract table
7. Pivot tables (crew_allotees)

This ensures all foreign key dependencies are satisfied during migration.
