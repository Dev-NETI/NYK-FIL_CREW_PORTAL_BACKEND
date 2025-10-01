<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SeedUserData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:user-data {--fresh : Truncate tables before seeding}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed user profile data (profiles, contacts, employment, education, physical traits)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('fresh')) {
            $this->info('Truncating user data tables...');
            \DB::table('user_physical_traits')->truncate();
            \DB::table('user_education')->truncate();
            \DB::table('user_employment')->truncate();
            \DB::table('user_contacts')->truncate();
            \DB::table('user_profiles')->truncate();
        }

        $this->info('Seeding user data...');
        
        $this->call('db:seed', ['--class' => 'UserProfileSeeder']);
        $this->call('db:seed', ['--class' => 'UserContactSeeder']);
        $this->call('db:seed', ['--class' => 'UserEmploymentSeeder']);
        $this->call('db:seed', ['--class' => 'UserEducationSeeder']);
        $this->call('db:seed', ['--class' => 'UserPhysicalTraitSeeder']);
        
        $this->info('User data seeded successfully!');
        
        // Show a sample user
        $user = \App\Models\User::with([
            'profile', 'contacts', 'employment.fleet', 'employment.rank', 
            'education.graduatedSchool', 'physicalTraits'
        ])->first();
        
        if ($user) {
            $this->info("\nSample user data:");
            $this->line("Email: {$user->email}");
            $this->line("Name: {$user->profile->first_name} {$user->profile->last_name}");
            $this->line("Mobile: {$user->contacts->mobile_number}");
            $this->line("Status: {$user->employment->crew_status}");
            $this->line("Physical: {$user->physicalTraits->height}cm, {$user->physicalTraits->blood_type}");
        }
    }
}
