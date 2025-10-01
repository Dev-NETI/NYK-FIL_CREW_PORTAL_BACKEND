<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, migrate existing data to new tables
        $this->migrateExistingData();
        
        // Then remove the columns from users table
        // First drop foreign key constraints
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['permanent_address_id']);
            $table->dropForeign(['fleet_id']);
            $table->dropForeign(['rank_id']);
            $table->dropForeign(['primary_allotee_id']);
            $table->dropForeign(['graduated_school_id']);
        });
        
        // Then drop the columns that actually exist in the current users table
        Schema::table('users', function (Blueprint $table) {
            // Remove contact information fields (now in user_contacts)
            $table->dropColumn([
                'mobile_number',
                'permanent_address_id'
            ]);
            
            // Remove employment information fields (now in user_employment)
            $table->dropColumn([
                'fleet_id',
                'rank_id',
                'crew_status',
                'hire_status',
                'hire_date',
                'passport_number',
                'passport_expiry',
                'seaman_book_number',
                'seaman_book_expiry',
                'primary_allotee_id'
            ]);
            
            // Remove education information fields (now in user_education)
            $table->dropColumn([
                'graduated_school_id',
                'date_graduated'
            ]);
        });
    }
    
    private function migrateExistingData()
    {
        // Get all existing users
        $users = DB::table('users')->get();
        
        foreach ($users as $user) {
            // Since current users table doesn't have name fields, create empty profiles for all users
            // This will be populated later by users themselves or admins
            DB::table('user_profiles')->insert([
                'user_id' => $user->id,
                'crew_id' => null, // Will be set later
                'first_name' => null,
                'middle_name' => null,
                'last_name' => null,
                'suffix' => null,
                'date_of_birth' => null,
                'age' => null,
                'gender' => null,
                'modified_by' => $user->modified_by ?? null,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]);
            
            // Migrate to user_contacts
            if ($user->mobile_number || $user->permanent_address_id) {
                DB::table('user_contacts')->insert([
                    'user_id' => $user->id,
                    'mobile_number' => $user->mobile_number,
                    'permanent_address_id' => $user->permanent_address_id,
                    'modified_by' => $user->modified_by ?? null,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]);
            }
            
            // Migrate to user_employment
            if ($user->is_crew && ($user->fleet_id || $user->rank_id || $user->crew_status)) {
                DB::table('user_employment')->insert([
                    'user_id' => $user->id,
                    'fleet_id' => $user->fleet_id,
                    'rank_id' => $user->rank_id,
                    'crew_status' => $user->crew_status,
                    'hire_status' => $user->hire_status,
                    'hire_date' => $user->hire_date,
                    'passport_number' => $user->passport_number,
                    'passport_expiry' => $user->passport_expiry,
                    'seaman_book_number' => $user->seaman_book_number,
                    'seaman_book_expiry' => $user->seaman_book_expiry,
                    'primary_allotee_id' => $user->primary_allotee_id,
                    'modified_by' => $user->modified_by ?? null,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]);
            }
            
            // Migrate to user_education
            if ($user->graduated_school_id || $user->date_graduated) {
                DB::table('user_education')->insert([
                    'user_id' => $user->id,
                    'graduated_school_id' => $user->graduated_school_id,
                    'date_graduated' => $user->date_graduated,
                    'modified_by' => $user->modified_by ?? null,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back the columns to users table
        Schema::table('users', function (Blueprint $table) {
            // Personal information fields
            $table->string('crew_id')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('suffix')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->integer('age')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            
            // Contact information fields
            $table->string('mobile_number')->nullable();
            $table->foreignId('permanent_address_id')->nullable()->constrained('addresses')->onDelete('set null');
            
            // Employment information fields
            $table->foreignId('fleet_id')->nullable()->constrained('fleets')->onDelete('set null');
            $table->foreignId('rank_id')->nullable()->constrained('ranks')->onDelete('set null');
            $table->enum('crew_status', ['on_board', 'on_vacation', 'standby', 'resigned', 'terminated'])->nullable();
            $table->enum('hire_status', ['new_hire', 're_hire', 'promoted', 'transferred'])->nullable();
            $table->date('hire_date')->nullable();
            $table->string('passport_number')->nullable();
            $table->date('passport_expiry')->nullable();
            $table->string('seaman_book_number')->nullable();
            $table->date('seaman_book_expiry')->nullable();
            $table->foreignId('primary_allotee_id')->nullable()->constrained('allotees')->onDelete('set null');
            
            // Education information fields
            $table->foreignId('graduated_school_id')->nullable()->constrained('universities')->onDelete('set null');
            $table->date('date_graduated')->nullable();
        });
        
        // Migrate data back from new tables
        $this->migrateDataBack();
    }
    
    private function migrateDataBack()
    {
        // Get all users and their related data
        $userProfiles = DB::table('user_profiles')->get();
        $userContacts = DB::table('user_contacts')->get();
        $userEmployment = DB::table('user_employment')->get();
        $userEducation = DB::table('user_education')->get();
        
        // Group by user_id for easier processing
        $profilesByUser = $userProfiles->keyBy('user_id');
        $contactsByUser = $userContacts->keyBy('user_id');
        $employmentByUser = $userEmployment->keyBy('user_id');
        $educationByUser = $userEducation->keyBy('user_id');
        
        // Update users table with data from separate tables
        $users = DB::table('users')->get();
        foreach ($users as $user) {
            $updateData = [];
            
            // From user_profiles
            if (isset($profilesByUser[$user->id])) {
                $profile = $profilesByUser[$user->id];
                $updateData = array_merge($updateData, [
                    'crew_id' => $profile->crew_id,
                    'first_name' => $profile->first_name,
                    'middle_name' => $profile->middle_name,
                    'last_name' => $profile->last_name,
                    'suffix' => $profile->suffix,
                    'date_of_birth' => $profile->date_of_birth,
                    'age' => $profile->age,
                    'gender' => $profile->gender,
                ]);
            }
            
            // From user_contacts
            if (isset($contactsByUser[$user->id])) {
                $contact = $contactsByUser[$user->id];
                $updateData = array_merge($updateData, [
                    'mobile_number' => $contact->mobile_number,
                    'permanent_address_id' => $contact->permanent_address_id,
                ]);
            }
            
            // From user_employment
            if (isset($employmentByUser[$user->id])) {
                $employment = $employmentByUser[$user->id];
                $updateData = array_merge($updateData, [
                    'fleet_id' => $employment->fleet_id,
                    'rank_id' => $employment->rank_id,
                    'crew_status' => $employment->crew_status,
                    'hire_status' => $employment->hire_status,
                    'hire_date' => $employment->hire_date,
                    'passport_number' => $employment->passport_number,
                    'passport_expiry' => $employment->passport_expiry,
                    'seaman_book_number' => $employment->seaman_book_number,
                    'seaman_book_expiry' => $employment->seaman_book_expiry,
                    'primary_allotee_id' => $employment->primary_allotee_id,
                ]);
            }
            
            // From user_education
            if (isset($educationByUser[$user->id])) {
                $education = $educationByUser[$user->id];
                $updateData = array_merge($updateData, [
                    'graduated_school_id' => $education->graduated_school_id,
                    'date_graduated' => $education->date_graduated,
                ]);
            }
            
            if (!empty($updateData)) {
                DB::table('users')->where('id', $user->id)->update($updateData);
            }
        }
    }
};
