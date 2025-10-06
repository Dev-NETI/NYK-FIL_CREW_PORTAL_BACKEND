<?php

namespace App\Traits;

trait FormatsUserData
{
    private function formatUserData($user): array
    {
        return [
            // Core user data
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at,
            'last_login_at' => $user->last_login_at,
            'last_login_ip' => $user->last_login_ip,
            'is_crew' => $user->is_crew,
            'role' => $user->is_crew ? 'crew' : 'admin',

            // Profile information
            'profile' => $user->profile ? [
                'crew_id' => $user->profile->crew_id,
                'first_name' => $user->profile->first_name,
                'middle_name' => $user->profile->middle_name,
                'last_name' => $user->profile->last_name,
                'suffix' => $user->profile->suffix,
                'date_of_birth' => $user->profile->date_of_birth,
                'age' => $user->profile->age,
                'gender' => $user->profile->gender,
                'full_name' => $user->profile->full_name,
            ] : null,

            // Contact information
            'contacts' => $user->contacts ? [
                'mobile_number' => $user->contacts->mobile_number,
                'alternate_phone' => $user->contacts->alternate_phone,
                'email_personal' => $user->contacts->email_personal,
                'permanent_address_id' => $user->contacts->permanent_address_id,
                'current_address_id' => $user->contacts->current_address_id,
                'emergency_contact_name' => $user->contacts->emergency_contact_name,
                'emergency_contact_phone' => $user->contacts->emergency_contact_phone,
                'emergency_contact_relationship' => $user->contacts->emergency_contact_relationship,
            ] : null,

            // Employment information
            'employment' => $user->employment ? [
                'fleet_id' => $user->employment->fleet_id,
                'fleet_name' => optional($user->employment->fleet)->name,
                'rank_id' => $user->employment->rank_id,
                'rank_name' => optional($user->employment->rank)->name,
                'crew_status' => $user->employment->crew_status,
                'hire_status' => $user->employment->hire_status,
                'hire_date' => $user->employment->hire_date,
                'passport_number' => $user->employment->passport_number,
                'passport_expiry' => $user->employment->passport_expiry,
                'seaman_book_number' => $user->employment->seaman_book_number,
                'seaman_book_expiry' => $user->employment->seaman_book_expiry,
                'primary_allotee_id' => $user->employment->primary_allotee_id,
                'basic_salary' => $user->employment->basic_salary,
                'employment_notes' => $user->employment->employment_notes,
            ] : null,

            // Education information
            'education' => $user->education ? [
                'graduated_school_id' => $user->education->graduated_school_id,
                'date_graduated' => $user->education->date_graduated,
                'degree' => $user->education->degree,
                'field_of_study' => $user->education->field_of_study,
                'gpa' => $user->education->gpa,
                'education_level' => $user->education->education_level,
                'certifications' => $user->education->certifications,
                'additional_training' => $user->education->additional_training,
            ] : null,

            // Physical traits
            'physical_traits' => $user->physicalTraits ? [
                'height' => $user->physicalTraits->height,
                'weight' => $user->physicalTraits->weight,
                'blood_type' => $user->physicalTraits->blood_type,
                'eye_color' => $user->physicalTraits->eye_color,
                'hair_color' => $user->physicalTraits->hair_color,
                'distinguishing_marks' => $user->physicalTraits->distinguishing_marks,
                'medical_conditions' => $user->physicalTraits->medical_conditions,
            ] : null,
        ];
    }
}
