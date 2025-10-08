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
            // Geographic data (no dependencies)
            IslandSeeder::class,
            RegionSeeder::class,
            ProvinceSeeder::class,
            CitySeeder::class,

            // Basic entity data (no dependencies)
            VesselTypeSeeder::class,

            UniversitySeeder::class,
            ProgramSeeder::class,

            // Rank hierarchy (dependencies within rank system)
            RankCategorySeeder::class,
            RankGroupSeeder::class,
            RankSeeder::class,

            // Address data (depends on geographic data)
            AddressSeeder::class,

            // User data (depends on fleet, rank, university, address)
            UserSeeder::class,

            // User profile data (depends on users)
            UserProfileSeeder::class,
            UserContactSeeder::class,
            UserEmploymentSeeder::class,
            UserEducationSeeder::class,
            UserPhysicalTraitSeeder::class,



            // Allotee data (independent)
            AlloteeSeeder::class,

            // Pivot table data (depends on users and allotees)
            // commented causing errors
            // CrewAlloteeSeeder::class,

            // Contract data (depends on users and vessels)
            DepartmentCategorySeeder::class,
            DepartmentSeeder::class,
            ContractSeeder::class,
            TravelDocumentTypeSeeder::class,
            TravelDocumentSeeder::class,
            EmploymentDocumentTypeSeeder::class,
            EmploymentDocumentSeeder::class,
            CertificateDocumentTypeSeeder::class,
            FleetSeeder::class,
            // Vessel data (depends on vessel types)
            VesselSeeder::class,
            CertificateTypeSeeder::class,
            CertificateSeeder::class
        ]);
    }
}
