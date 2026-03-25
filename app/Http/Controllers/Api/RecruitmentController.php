<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CrewCertificate;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserProgramEmployment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecruitmentController extends Controller
{
    /**
     * Ingest a new crew member from the Recruitment App.
     *
     * Creates records across all core crew models in a single atomic transaction.
     * Travel documents, crew certificates, and employment documents are optional.
     *
     * POST /api/recruitment/ingest
     */
    public function ingest(Request $request)
    {
        // ── Duplicate crew guard (before full validation) ──────────────────────
        if ($request->filled('email') && User::where('email', $request->input('email'))->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'A crew member with this email already exists in the system.',
            ], 409);
        }

        if ($request->filled('profile.crew_id') && UserProfile::where('crew_id', $request->input('profile.crew_id'))->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'A crew member with this crew ID already exists in the system.',
            ], 409);
        }

        try {
            $validated = $request->validate([
                // ── User ──────────────────────────────────────────────────────
                'email'         => 'required|email|max:255|unique:users,email',
                'is_industrial' => 'nullable|boolean',

                // ── UserProfile ───────────────────────────────────────────────
                'profile'                => 'required|array',
                'profile.crew_id'        => 'required|string|max:255|unique:user_profiles,crew_id',
                'profile.first_name'     => 'required|string|max:255',
                'profile.middle_name'    => 'nullable|string|max:255',
                'profile.last_name'      => 'required|string|max:255',
                'profile.suffix'         => 'nullable|string|max:50',
                'profile.birth_date'     => 'required|date',
                'profile.birth_place'    => 'required|string|max:255',
                'profile.age'            => 'nullable|integer|min:0|max:150',
                'profile.gender'         => 'required|in:Male,Female',
                'profile.nationality'    => 'nullable|string|max:100',
                'profile.civil_status'   => 'required|string|max:100',
                'profile.religion'       => 'nullable|string|max:100',
                'profile.blood_type'     => 'nullable|string|max:10',
                'profile.rank_id'        => 'nullable|integer|exists:ranks,id',
                'profile.fleet_id'       => 'nullable|integer|exists:fleets,id',
                'profile.company_id'     => 'nullable|integer|exists:companies,id',
                'profile.image_path'     => 'nullable|string|max:500',

                // ── UserContact ───────────────────────────────────────────────
                'contact'                                       => 'nullable|array',
                'contact.mobile_number'                         => 'nullable|string|max:20',
                'contact.alternate_phone'                       => 'nullable|string|max:20',
                'contact.emergency_contact_name'                => 'nullable|string|max:255',
                'contact.emergency_contact_phone'               => 'nullable|string|max:20',
                'contact.emergency_contact_relationship'        => 'nullable|string|max:100',

                // ── UserPhysicalTrait ─────────────────────────────────────────
                'physical_traits'            => 'nullable|array',
                'physical_traits.height'     => 'nullable|numeric|min:0|max:300',
                'physical_traits.weight'     => 'nullable|numeric|min:0|max:500',
                'physical_traits.eye_color'  => 'nullable|string|max:20',
                'physical_traits.hair_color' => 'nullable|string|max:20',
                'physical_traits.blood_type' => 'nullable|string|max:5',

                // ── UserEmployment ────────────────────────────────────────────
                'employment'                    => 'nullable|array',
                'employment.rank_id'            => 'nullable|integer|exists:ranks,id',
                'employment.fleet_id'           => 'nullable|integer|exists:fleets,id',
                'employment.crew_status'        => 'nullable|in:on_board,on_vacation,standby,resigned,terminated',
                'employment.hire_status'        => 'nullable|in:new_hire,re_hire,promoted,transferred',
                'employment.hire_date'          => 'nullable|date',
                'employment.passport_number'    => 'nullable|string|max:255',
                'employment.passport_expiry'    => 'nullable|date',
                'employment.seaman_book_number' => 'nullable|string|max:255',
                'employment.seaman_book_expiry' => 'nullable|date',
                'employment.basic_salary'       => 'nullable|numeric|min:0',
                'employment.employment_notes'   => 'nullable|string',

                // ── UserEducation (array — one entry per level) ───────────────
                'education'                   => 'nullable|array',
                'education.*.education_level' => 'required|in:high_school,college,higher_educational',
                'education.*.school_name'     => 'nullable|string|max:255',
                'education.*.degree'          => 'nullable|string|max:255',
                'education.*.date_graduated'  => 'nullable|date',

                // ── UserProgramEmployment (array) ─────────────────────────────
                'program_employments'              => 'nullable|array',
                'program_employments.*.program_id' => 'required|integer|exists:programs,id',
                'program_employments.*.batch'      => 'nullable|string|max:255',

                // ── TravelDocument (optional array — file_path only) ──────────
                'travel_documents'                            => 'nullable|array',
                'travel_documents.*.travel_document_type_id' => 'required|integer|exists:travel_document_types,id',
                'travel_documents.*.id_no'                   => 'nullable|string|max:255',
                'travel_documents.*.place_of_issue'          => 'nullable|string|max:500',
                'travel_documents.*.date_of_issue'           => 'nullable|date',
                'travel_documents.*.expiration_date'         => 'nullable|date',
                'travel_documents.*.remaining_pages'         => 'nullable|integer|min:0',
                'travel_documents.*.is_US_VISA'              => 'nullable|boolean',
                'travel_documents.*.visa_type'               => 'nullable|string|max:255',
                'travel_documents.*.file_path'               => 'nullable|string|max:500',
                'travel_documents.*.file_ext'                => 'nullable|string|max:10',

                // ── CrewCertificate (optional array — file_path only) ─────────
                'crew_certificates'                    => 'nullable|array',
                'crew_certificates.*.certificate_id'   => 'required|integer|exists:certificates,id',
                'crew_certificates.*.grade'            => 'nullable|string',
                'crew_certificates.*.rank_permitted'   => 'nullable|string',
                'crew_certificates.*.certificate_no'   => 'nullable|string',
                'crew_certificates.*.issued_by'        => 'nullable|string',
                'crew_certificates.*.date_issued'      => 'nullable|date',
                'crew_certificates.*.expiry_date'      => 'nullable|date',
                'crew_certificates.*.file_path'        => 'nullable|string|max:500',
                'crew_certificates.*.file_ext'         => 'nullable|string|max:10',

                // ── EmploymentDocument (optional array — file_path only) ───────
                'employment_documents'                                 => 'nullable|array',
                'employment_documents.*.employment_document_type_id'   => 'required|integer|exists:employment_document_types,id',
                'employment_documents.*.document_number'               => 'nullable|string',
                'employment_documents.*.file_path'                     => 'nullable|string|max:500',
                'employment_documents.*.file_ext'                      => 'nullable|string|max:10',
            ]);

            DB::beginTransaction();

            try {
                // 1. User
                $user = User::create([
                    'email'         => $validated['email'],
                    'is_crew'       => true,
                    'is_industrial' => $validated['is_industrial'] ?? false,
                    'modified_by'   => 'RECRUITMENT API',
                ]);

                // 2. UserProfile
                $profileData = $validated['profile'];
                $profile = $user->profile()->create([
                    'crew_id'      => $profileData['crew_id'],
                    'first_name'   => $profileData['first_name'],
                    'middle_name'  => $profileData['middle_name'] ?? null,
                    'last_name'    => $profileData['last_name'],
                    'suffix'       => $profileData['suffix'] ?? null,
                    'birth_date'   => $profileData['birth_date'],
                    'birth_place'  => $profileData['birth_place'],
                    'age'          => $profileData['age'] ?? null,
                    'gender'       => $profileData['gender'],
                    'nationality'  => $profileData['nationality'] ?? null,
                    'civil_status' => $profileData['civil_status'],
                    'religion'     => $profileData['religion'] ?? null,
                    'blood_type'   => $profileData['blood_type'] ?? null,
                    'rank_id'      => $profileData['rank_id'] ?? null,
                    'fleet_id'     => $profileData['fleet_id'] ?? null,
                    'company_id'   => $profileData['company_id'] ?? null,
                    'image_path'   => $profileData['image_path'] ?? null,
                    'modified_by'  => 'RECRUITMENT API',
                ]);

                $crewId = $profile->crew_id;

                // 3. UserContact
                if (!empty($validated['contact'])) {
                    $user->contacts()->create(array_merge(
                        $validated['contact'],
                        ['modified_by' => 'RECRUITMENT API']
                    ));
                }

                // 4. UserPhysicalTrait
                if (!empty($validated['physical_traits'])) {
                    $user->physicalTraits()->create(array_merge(
                        $validated['physical_traits'],
                        ['modified_by' => 'RECRUITMENT API']
                    ));
                }

                // 5. UserEmployment
                if (!empty($validated['employment'])) {
                    $user->employment()->create(array_merge(
                        $validated['employment'],
                        ['modified_by' => 'RECRUITMENT API']
                    ));
                }

                // 6. UserEducation (multiple)
                if (!empty($validated['education'])) {
                    foreach ($validated['education'] as $edu) {
                        $user->educations()->create(array_merge(
                            $edu,
                            ['modified_by' => 'RECRUITMENT API']
                        ));
                    }
                }

                // 7. UserProgramEmployment (multiple)
                if (!empty($validated['program_employments'])) {
                    foreach ($validated['program_employments'] as $pe) {
                        UserProgramEmployment::create([
                            'user_id'     => $user->id,
                            'program_id'  => $pe['program_id'],
                            'batch'       => $pe['batch'] ?? null,
                            'modified_by' => 'RECRUITMENT API',
                        ]);
                    }
                }

                // 8. TravelDocuments (optional)
                if (!empty($validated['travel_documents'])) {
                    foreach ($validated['travel_documents'] as $doc) {
                        $profile->travelDocuments()->create(array_merge(
                            $doc,
                            [
                                'crew_id'     => $crewId,
                                'modified_by' => 'RECRUITMENT API',
                            ]
                        ));
                    }
                }

                // 9. CrewCertificates (optional)
                if (!empty($validated['crew_certificates'])) {
                    foreach ($validated['crew_certificates'] as $cert) {
                        CrewCertificate::create(array_merge(
                            $cert,
                            [
                                'crew_id'     => $crewId,
                                'modified_by' => 'RECRUITMENT API',
                            ]
                        ));
                    }
                }

                // 10. EmploymentDocuments (optional)
                if (!empty($validated['employment_documents'])) {
                    foreach ($validated['employment_documents'] as $doc) {
                        $profile->employmentDocuments()->create(array_merge(
                            $doc,
                            [
                                'crew_id'     => $crewId,
                                'modified_by' => 'RECRUITMENT API',
                            ]
                        ));
                    }
                }

                DB::commit();

                Log::info('Recruitment API: crew member ingested', [
                    'user_id' => $user->id,
                    'crew_id' => $crewId,
                    'email'   => $user->email,
                    'ip'      => $request->ip(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Crew member ingested successfully',
                    'data'    => [
                        'user_id' => $user->id,
                        'crew_id' => $crewId,
                        'email'   => $user->email,
                    ],
                ], 201);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Recruitment API: ingest failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip'    => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while ingesting crew data',
                'debug'   => config('app.debug') ? [
                    'message' => $e->getMessage(),
                    'line'    => $e->getLine(),
                    'file'    => basename($e->getFile()),
                ] : null,
            ], 500);
        }
    }
}
