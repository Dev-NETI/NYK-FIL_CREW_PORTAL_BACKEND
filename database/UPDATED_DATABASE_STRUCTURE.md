# Updated Crew Portal Database Structure

## Overview

This document describes the **updated** normalized database structure for the Crew Portal Backend. The main change is that **crew functionality has been merged into the `users` table**, eliminating the separate `crew` table and simplifying authentication.

## Key Changes

### ✅ **USERS TABLE NOW INCLUDES CREW FUNCTIONALITY**

-   The `users` table has been extended with all crew-related fields
-   Users with a `crew_id` are crew members
-   Users without a `crew_id` are admin/staff users
-   Single table for both authentication and crew management

### ✅ **SIMPLIFIED RELATIONSHIPS**

-   `contracts` now references `users` instead of `crew`
-   `user_allotees` pivot table instead of `crew_allotees`
-   All other models updated to reference `users`

## Current Database Tables (23 tables)

### **Core Business Tables:**

-   ✅ `users` - **Enhanced with crew functionality** (authentication + crew data)
-   ✅ `contracts` - Vessel assignments (now references `users`)
-   ✅ `allotees` - Emergency contacts and beneficiaries
-   ✅ `user_allotees` - Many-to-many relationship with allotment details

### **Geographic Hierarchy:**

-   ✅ `islands` - Luzon, Visayas, Mindanao
-   ✅ `regions` - Administrative regions
-   ✅ `provinces` - Provinces within regions
-   ✅ `cities` - Cities/municipalities
-   ✅ `addresses` - Flexible address storage

### **Reference Data:**

-   ✅ `schools` - Educational institutions
-   ✅ `rank_categories` - Officer, Rating, Management
-   ✅ `rank_groups` - Deck, Engine, Catering
-   ✅ `ranks` - Specific positions

### **Vessel Management:**

-   ✅ `vessel_types` - Tanker, Container, Bulk Carrier
-   ✅ `fleets` - Fleet organization
-   ✅ `vessels` - Individual vessel information

### **System Tables:**

-   ✅ `users` - **Enhanced authentication + crew data**
-   ✅ All Laravel framework tables (cache, jobs, sessions, etc.)

## Enhanced Users Table Structure

The `users` table now contains:

### **Standard Laravel Auth Fields:**

-   `id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`

### **Crew-Specific Fields:**

-   `crew_id` - Unique crew identifier (nullable - only for crew members)
-   `first_name`, `middle_name`, `last_name`, `suffix` - Name components
-   `date_of_birth`, `age`, `gender` - Personal info
-   `mobile_number`, `alternative_mobile` - Contact info
-   `permanent_address_id` - Foreign key to addresses
-   `graduated_school_id` - Foreign key to schools
-   `date_graduated`, `course_degree` - Education info
-   `crew_status` - active, on_leave, resigned, terminated, retired
-   `hire_status` - hired, candidate, interview, rejected, on_hold
-   `hire_date` - Employment start date
-   `passport_number`, `passport_expiry` - Travel documents
-   `seaman_book_number`, `seaman_book_expiry` - Maritime documents
-   `primary_allotee_id` - Foreign key to primary emergency contact
-   `is_active` - Active status flag

## Model Relationships

### **User Model (Enhanced)**

```php
// Authentication (standard Laravel)
User::find(1)->name;
User::find(1)->email;

// Crew functionality
User::find(1)->crew_id; // Crew identifier
User::find(1)->permanentAddress(); // Address relationship
User::find(1)->graduatedSchool(); // School relationship
User::find(1)->allotees(); // Many-to-many allotees
User::find(1)->contracts(); // Contracts/assignments
User::find(1)->currentContract(); // Active contract
User::find(1)->currentVessel(); // Current vessel assignment
User::find(1)->currentRank(); // Current rank

// Scopes
User::crew()->get(); // Only crew members (have crew_id)
User::active()->get(); // Active crew members
User::hired()->get(); // Hired crew members
```

### **Contract Model (Updated)**

```php
// Now references users instead of crew
$contract->user(); // Get the crew member (user)
$contract->vessel(); // Get assigned vessel
$contract->rank(); // Get assigned rank
```

## Usage Examples

### **Creating a Crew Member**

```php
$user = User::create([
    'crew_id' => 'CR-2024-001',
    'first_name' => 'Juan',
    'last_name' => 'Dela Cruz',
    'email' => 'juan@crewportal.com',
    'password' => Hash::make('password'),
    'date_of_birth' => '1990-01-15',
    'gender' => 'male',
    'mobile_number' => '+639171234567',
    'crew_status' => 'active',
    'hire_status' => 'hired',
    'hire_date' => now(),
]);
```

### **Creating an Admin User**

```php
$admin = User::create([
    'name' => 'Admin User',
    'email' => 'admin@crewportal.com',
    'password' => Hash::make('password'),
    // No crew_id - this is an admin/staff user
]);
```

### **Assigning Crew to Vessel**

```php
$contract = Contract::create([
    'contract_number' => 'CNT-2024-001',
    'user_id' => $user->id, // References users table
    'vessel_id' => $vessel->id,
    'rank_id' => $rank->id,
    'duration_months' => 12,
    'contract_start_date' => now(),
    'basic_salary' => 2500.00,
    'status' => 'active'
]);
```

### **Querying Crew Data**

```php
// Get all crew members (users with crew_id)
$crewMembers = User::crew()->get();

// Get active crew
$activeCrew = User::active()->get();

// Get crew from specific region
$ncrCrew = User::crew()
    ->whereHas('permanentAddress.city.province.region', function($query) {
        $query->where('name', 'NCR');
    })->get();

// Get crew on specific vessel
$vesselCrew = $vessel->currentCrew();

// Get contracts expiring soon
$expiring = Contract::expiringSoon(30)->get();
```

### **Authentication & Authorization**

```php
// Login works the same for all users
Auth::attempt(['email' => 'juan@crewportal.com', 'password' => 'password']);

// Check if authenticated user is crew
if (Auth::user()->crew_id) {
    // This is a crew member
    $currentVessel = Auth::user()->currentVessel();
} else {
    // This is admin/staff
}
```

## Migration Summary

The following migrations were applied to achieve this structure:

1. ✅ **Added crew fields to users table** - Extended users with all crew attributes
2. ✅ **Updated contracts table** - Changed `crew_id` to `user_id`
3. ✅ **Created user_allotees table** - New pivot table for user ↔ allotee relationships
4. ✅ **Dropped crew tables** - Removed separate `crew` and `crew_allotees` tables
5. ✅ **Updated all models** - Changed relationships to reference `users`

## Benefits of This Approach

### ✅ **Simplified Authentication**

-   Single login system for both crew and admin users
-   No need to manage separate user ↔ crew relationships
-   Cleaner authorization logic

### ✅ **Reduced Complexity**

-   One less table to manage
-   Fewer join operations for crew data
-   Simplified data integrity

### ✅ **Better Performance**

-   Direct access to crew data through users table
-   Fewer database queries for crew operations
-   More efficient indexing

### ✅ **Flexible User Types**

-   Users with `crew_id` = crew members
-   Users without `crew_id` = admin/staff
-   Easy to add more user types in the future

## Data Integrity

-   All foreign key constraints maintained
-   Proper indexing on frequently queried fields
-   Backward compatibility through model aliases
-   Validation at application level for crew-specific fields

Your crew portal database is now optimized with users serving as both authentication and crew management! 🚢⚓️
