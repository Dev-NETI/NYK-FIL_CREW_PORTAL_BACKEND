<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run seeders in the correct order to respect foreign key constraints
        $this->call([
            // ── Reference / lookup tables (no FK dependencies) ───────────────
            CompanySeeder::class,
            NationalitySeeder::class,
            VesselTypeSeeder::class,
            RankDepartmentSeeder::class,
            RankTypeSeeder::class,
            ProgramSeeder::class,
            DepartmentCategorySeeder::class,
            TravelDocumentTypeSeeder::class,
            EmploymentDocumentTypeSeeder::class,
            CertificateTypeSeeder::class,
            RoleSeeder::class,
            AppointmentTypeSeeder::class,

            // ── Geographic hierarchy (region → province → city → barangay) ───
            RegionSeeder::class,
            ProvinceSeeder::class,
            CitySeeder::class,
            BarangaySeeder::class,

            // ── Rank hierarchy (depends on RankDepartment, RankType) ─────────
            RankSeeder::class,
            RankLevelingSeeder::class,

            // ── Department hierarchy (depends on DepartmentCategory) ──────────
            DepartmentSeeder::class,

            // ── Fleet data (depends on Department) ───────────────────────────
            FleetSeeder::class,

            // ── Vessel data (depends on VesselType, Fleet) ───────────────────
            VesselSeeder::class,

            // ── Address data (depends on geographic data) ────────────────────
            AddressSeeder::class,

            // ── Users (base entity) ──────────────────────────────────────────
            UserSeeder::class,

            // ── User profile & related (depends on User, Rank, Fleet, Company)
            UserProfileSeeder::class,
            UserContactSeeder::class,
            UserEmploymentSeeder::class,
            UserEducationSeeder::class,
            UserPhysicalTraitSeeder::class,

            // ── Admin users (depends on User, Role) ──────────────────────────
            AdminProfileSeeder::class,
            AdminRoleSeeder::class,

            // ── Allotees (depends on User) ───────────────────────────────────
            AlloteeSeeder::class,
            // CrewAlloteeSeeder::class, // commented - causing errors

            // ── Contracts (depends on User, Vessel) ──────────────────────────
            ContractSeeder::class,

            // ── Documents (depend on UserProfile / crew_id) ──────────────────
            TravelDocumentSeeder::class,
            EmploymentDocumentSeeder::class,
            CertificateSeeder::class,
        ]);
    }
}
