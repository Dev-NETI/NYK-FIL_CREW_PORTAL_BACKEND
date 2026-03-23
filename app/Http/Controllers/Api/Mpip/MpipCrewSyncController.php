<?php

namespace App\Http\Controllers\Api\Mpip;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Allotee;
use App\Models\Certificate;
use App\Models\Company;
use App\Models\CrewCertificate;
use App\Models\EmploymentDocument;
use App\Models\EmploymentDocumentType;
use App\Models\Fleet;
use App\Models\Program;
use App\Models\Rank;
use App\Models\TravelDocument;
use App\Models\TravelDocumentType;
use App\Models\User;
use App\Models\UserContact;
use App\Models\UserEducation;
use App\Models\UserEmployment;
use App\Models\UserProfile;
use App\Models\UserProgramEmployment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MpipCrewSyncController extends Controller
{
    // ─── Lookup caches (populated once per request) ──────────────────────────
    private array $ranksByCode        = [];
    private array $fleetsByName       = [];
    private array $companiesByName    = [];
    private array $travelDocTypes     = [];
    private array $employmentDocTypes = [];
    private array $certificatesByCode = [];
    private array $programsByName     = [];

    /**
     * Sync comprehensive crew data received from MPIP.
     *
     * POST /api/mpip/crew/sync
     */
    public function sync(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'crew'         => 'required|array|min:1',
            'crew.*.email' => 'required|email',

            // Basic profile
            'crew.*.profile'                  => 'nullable|array',
            'crew.*.profile.first_name'       => 'required_with:crew.*.profile|string|max:255',
            'crew.*.profile.last_name'        => 'required_with:crew.*.profile|string|max:255',
            'crew.*.profile.middle_name'      => 'nullable|string|max:255',
            'crew.*.profile.suffix'           => 'nullable|string|max:50',
            'crew.*.profile.birth_date'       => 'nullable|date',
            'crew.*.profile.birth_place'      => 'nullable|string|max:255',
            'crew.*.profile.gender'           => 'nullable|string|max:10',
            'crew.*.profile.nationality'      => 'nullable|string|max:100',
            'crew.*.profile.civil_status'     => 'nullable|string|max:50',
            'crew.*.profile.religion'         => 'nullable|string|max:100',
            'crew.*.profile.blood_type'       => 'nullable|string|max:10',
            'crew.*.profile.rank_code'        => 'nullable|string|max:50',
            'crew.*.profile.fleet_name'       => 'nullable|string|max:255',
            'crew.*.profile.company_name'     => 'nullable|string|max:255',
            'crew.*.is_industrial'            => 'nullable|boolean',

            // Contact
            'crew.*.contact'                                   => 'nullable|array',
            'crew.*.contact.mobile_number'                     => 'nullable|string|max:50',
            'crew.*.contact.alternate_phone'                   => 'nullable|string|max:50',
            'crew.*.contact.emergency_contact_name'            => 'nullable|string|max:255',
            'crew.*.contact.emergency_contact_phone'           => 'nullable|string|max:50',
            'crew.*.contact.emergency_contact_relationship'    => 'nullable|string|max:100',

            // Addresses
            'crew.*.permanent_address'                  => 'nullable|array',
            'crew.*.permanent_address.full_address'     => 'nullable|string|max:500',
            'crew.*.permanent_address.street_address'   => 'nullable|string|max:255',
            'crew.*.permanent_address.zip_code'         => 'nullable|string|max:20',
            'crew.*.current_address'                    => 'nullable|array',
            'crew.*.current_address.full_address'       => 'nullable|string|max:500',
            'crew.*.current_address.street_address'     => 'nullable|string|max:255',
            'crew.*.current_address.zip_code'           => 'nullable|string|max:20',

            // Employment status
            'crew.*.employment'                         => 'nullable|array',
            'crew.*.employment.crew_status'             => 'nullable|string|max:50',
            'crew.*.employment.hire_status'             => 'nullable|string|max:50',
            'crew.*.employment.hire_date'               => 'nullable|date',
            'crew.*.employment.passport_number'         => 'nullable|string|max:100',
            'crew.*.employment.passport_expiry'         => 'nullable|date',
            'crew.*.employment.seaman_book_number'      => 'nullable|string|max:100',
            'crew.*.employment.seaman_book_expiry'      => 'nullable|date',
            'crew.*.employment.basic_salary'            => 'nullable|numeric|min:0',
            'crew.*.employment.employment_notes'        => 'nullable|string',

            // Education (array of records)
            'crew.*.education'                          => 'nullable|array',
            'crew.*.education.*.school_name'            => 'required_with:crew.*.education|string|max:255',
            'crew.*.education.*.date_graduated'         => 'nullable|date',
            'crew.*.education.*.degree'                 => 'nullable|string|max:255',
            'crew.*.education.*.education_level'        => 'nullable|string|max:100',

            // Allotees
            'crew.*.allotees'                           => 'nullable|array',
            'crew.*.allotees.*.name'                    => 'required_with:crew.*.allotees|string|max:255',
            'crew.*.allotees.*.relationship'            => 'nullable|string|max:100',
            'crew.*.allotees.*.mobile_number'           => 'nullable|string|max:50',
            'crew.*.allotees.*.email'                   => 'nullable|email',
            'crew.*.allotees.*.address'                 => 'nullable|string|max:500',
            'crew.*.allotees.*.date_of_birth'           => 'nullable|date',
            'crew.*.allotees.*.gender'                  => 'nullable|string|max:10',
            'crew.*.allotees.*.is_primary'              => 'nullable|boolean',
            'crew.*.allotees.*.is_emergency_contact'    => 'nullable|boolean',

            // Travel documents
            'crew.*.travel_documents'                           => 'nullable|array',
            'crew.*.travel_documents.*.document_type_name'     => 'required_with:crew.*.travel_documents|string|max:255',
            'crew.*.travel_documents.*.id_no'                  => 'required_with:crew.*.travel_documents|string|max:100',
            'crew.*.travel_documents.*.place_of_issue'         => 'nullable|string|max:255',
            'crew.*.travel_documents.*.date_of_issue'          => 'nullable|date',
            'crew.*.travel_documents.*.expiration_date'        => 'nullable|date',
            'crew.*.travel_documents.*.remaining_pages'        => 'nullable|integer',
            'crew.*.travel_documents.*.is_US_VISA'             => 'nullable|boolean',
            'crew.*.travel_documents.*.visa_type'              => 'nullable|string|max:100',
            'crew.*.travel_documents.*.file_path'              => 'nullable|string|max:500',
            'crew.*.travel_documents.*.file_ext'               => 'nullable|string|max:20',

            // Employment documents
            'crew.*.employment_documents'                               => 'nullable|array',
            'crew.*.employment_documents.*.document_type_name'         => 'required_with:crew.*.employment_documents|string|max:255',
            'crew.*.employment_documents.*.document_number'            => 'nullable|string|max:100',
            'crew.*.employment_documents.*.file_path'                  => 'nullable|string|max:500',
            'crew.*.employment_documents.*.file_ext'                   => 'nullable|string|max:20',

            // Certificates
            'crew.*.certificates'                           => 'nullable|array',
            'crew.*.certificates.*.certificate_code'        => 'required_with:crew.*.certificates|string|max:100',
            'crew.*.certificates.*.grade'                   => 'nullable|string|max:100',
            'crew.*.certificates.*.rank_permitted'          => 'nullable|string|max:100',
            'crew.*.certificates.*.certificate_no'          => 'nullable|string|max:100',
            'crew.*.certificates.*.issued_by'               => 'nullable|string|max:255',
            'crew.*.certificates.*.date_issued'             => 'nullable|date',
            'crew.*.certificates.*.expiry_date'             => 'nullable|date',
            'crew.*.certificates.*.file_path'               => 'nullable|string|max:500',
            'crew.*.certificates.*.file_ext'                => 'nullable|string|max:20',

            // Programs
            'crew.*.programs'                       => 'nullable|array',
            'crew.*.programs.*.program_name'        => 'required_with:crew.*.programs|string|max:255',
            'crew.*.programs.*.batch'               => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $this->loadLookupCaches();

        $results = ['updated' => [], 'skipped' => [], 'errors' => []];

        foreach ($request->crew as $crewData) {
            DB::transaction(function () use ($crewData, &$results) {
                try {
                    $user = User::where('email', $crewData['email'])
                        ->where('is_crew', true)
                        ->first();

                    if (! $user) {
                        $results['skipped'][] = [
                            'email'  => $crewData['email'],
                            'reason' => 'No crew member found with this email.',
                        ];
                        return;
                    }

                    // is_industrial flag
                    if (isset($crewData['is_industrial'])) {
                        $user->is_industrial = (bool) $crewData['is_industrial'];
                        $user->modified_by   = 'MPIP API';
                        $user->save();
                    }

                    // Run each section
                    $profile = $this->syncProfile($user, $crewData['profile'] ?? null);
                    $this->syncContact($user, $crewData['contact'] ?? null, $crewData['permanent_address'] ?? null, $crewData['current_address'] ?? null);
                    $this->syncEmployment($user, $crewData['employment'] ?? null);
                    $this->syncEducation($user, $crewData['education'] ?? null);
                    $this->syncAllotees($user, $crewData['allotees'] ?? null);

                    if ($profile?->crew_id) {
                        $crewId = $profile->crew_id;
                        $this->syncTravelDocuments($crewId, $crewData['travel_documents'] ?? null);
                        $this->syncEmploymentDocuments($crewId, $crewData['employment_documents'] ?? null);
                        $this->syncCertificates($crewId, $crewData['certificates'] ?? null);
                    }

                    $this->syncPrograms($user, $crewData['programs'] ?? null);

                    $results['updated'][] = $crewData['email'];
                } catch (\Throwable $e) {
                    $results['errors'][] = [
                        'email'   => $crewData['email'],
                        'message' => $e->getMessage(),
                    ];
                }
            });
        }

        return response()->json([
            'success' => true,
            'message' => 'Crew sync completed.',
            'summary' => [
                'updated' => count($results['updated']),
                'skipped' => count($results['skipped']),
                'errors'  => count($results['errors']),
            ],
            'details' => $results,
        ]);
    }

    // ─── Lookup cache loader ──────────────────────────────────────────────────

    private function loadLookupCaches(): void
    {
        $this->ranksByCode        = Rank::whereNotNull('code')->get(['id', 'code'])->keyBy('code')->all();
        $this->fleetsByName       = Fleet::get(['id', 'name'])->keyBy('name')->all();
        $this->companiesByName    = Company::get(['id', 'name'])->keyBy('name')->all();
        $this->travelDocTypes     = TravelDocumentType::get(['id', 'name'])->keyBy('name')->all();
        $this->employmentDocTypes = EmploymentDocumentType::get(['id', 'name'])->keyBy('name')->all();
        $this->certificatesByCode = Certificate::whereNotNull('code')->get(['id', 'code'])->keyBy('code')->all();
        $this->programsByName     = Program::get(['id', 'name'])->keyBy('name')->all();
    }

    // ─── Section handlers ─────────────────────────────────────────────────────

    private function syncProfile(User $user, ?array $data): ?UserProfile
    {
        if (empty($data)) {
            return $user->profile;
        }

        $rankId    = $data['rank_code']    ? ($this->ranksByCode[$data['rank_code']]?->id    ?? null) : null;
        $fleetId   = $data['fleet_name']   ? ($this->fleetsByName[$data['fleet_name']]?->id   ?? null) : null;
        $companyId = $data['company_name'] ? ($this->companiesByName[$data['company_name']]?->id ?? null) : null;

        $payload = array_filter([
            'first_name'   => $data['first_name']   ?? null,
            'middle_name'  => $data['middle_name']   ?? null,
            'last_name'    => $data['last_name']     ?? null,
            'suffix'       => $data['suffix']        ?? null,
            'birth_date'   => $data['birth_date']    ?? null,
            'birth_place'  => $data['birth_place']   ?? null,
            'gender'       => $data['gender']        ?? null,
            'nationality'  => $data['nationality']   ?? null,
            'civil_status' => $data['civil_status']  ?? null,
            'religion'     => $data['religion']      ?? null,
            'blood_type'   => $data['blood_type']    ?? null,
        ], fn($v) => $v !== null);

        if ($rankId)    $payload['rank_id']    = $rankId;
        if ($fleetId)   $payload['fleet_id']   = $fleetId;
        if ($companyId) $payload['company_id'] = $companyId;

        $profile = $user->profile ?? new UserProfile(['user_id' => $user->id]);
        $profile->fill($payload);
        $profile->modified_by = 'MPIP API';
        $profile->save();

        return $profile->refresh();
    }

    private function syncContact(User $user, ?array $data, ?array $permanentAddr, ?array $currentAddr): void
    {
        $contact = $user->contacts ?? new UserContact(['user_id' => $user->id]);

        // Upsert addresses and get their IDs
        $permanentAddressId = $contact->permanent_address_id;
        $currentAddressId   = $contact->current_address_id;

        if (! empty($permanentAddr)) {
            $permanentAddressId = $this->upsertAddress($user->id, 'permanent', $permanentAddr);
        }

        if (! empty($currentAddr)) {
            $currentAddressId = $this->upsertAddress($user->id, 'current', $currentAddr);
        }

        $contactPayload = [];
        if (! empty($data)) {
            $contactPayload = array_filter([
                'mobile_number'                  => $data['mobile_number']                  ?? null,
                'alternate_phone'                => $data['alternate_phone']                ?? null,
                'emergency_contact_name'         => $data['emergency_contact_name']         ?? null,
                'emergency_contact_phone'        => $data['emergency_contact_phone']        ?? null,
                'emergency_contact_relationship' => $data['emergency_contact_relationship'] ?? null,
            ], fn($v) => $v !== null);
        }

        if ($permanentAddressId) $contactPayload['permanent_address_id'] = $permanentAddressId;
        if ($currentAddressId)   $contactPayload['current_address_id']   = $currentAddressId;

        if (! empty($contactPayload)) {
            $contact->fill($contactPayload);
            $contact->modified_by = 'MPIP API';
            $contact->save();
        }
    }

    private function upsertAddress(int $userId, string $type, array $data): int
    {
        $address = Address::firstOrNew(['user_id' => $userId, 'type' => $type]);
        $address->fill(array_filter([
            'full_address'   => $data['full_address']   ?? null,
            'street_address' => $data['street_address'] ?? null,
            'zip_code'       => $data['zip_code']       ?? null,
        ], fn($v) => $v !== null));
        $address->modified_by = 'MPIP API';
        $address->save();

        return $address->id;
    }

    private function syncEmployment(User $user, ?array $data): void
    {
        if (empty($data)) {
            return;
        }

        $rankId  = isset($data['rank_code'])  ? ($this->ranksByCode[$data['rank_code']]?->id  ?? null) : null;
        $fleetId = isset($data['fleet_name']) ? ($this->fleetsByName[$data['fleet_name']]?->id ?? null) : null;

        $payload = array_filter([
            'crew_status'         => $data['crew_status']         ?? null,
            'hire_status'         => $data['hire_status']         ?? null,
            'hire_date'           => $data['hire_date']           ?? null,
            'passport_number'     => $data['passport_number']     ?? null,
            'passport_expiry'     => $data['passport_expiry']     ?? null,
            'seaman_book_number'  => $data['seaman_book_number']  ?? null,
            'seaman_book_expiry'  => $data['seaman_book_expiry']  ?? null,
            'basic_salary'        => $data['basic_salary']        ?? null,
            'employment_notes'    => $data['employment_notes']    ?? null,
        ], fn($v) => $v !== null);

        if ($rankId)  $payload['rank_id']  = $rankId;
        if ($fleetId) $payload['fleet_id'] = $fleetId;

        $employment = $user->employment ?? new UserEmployment(['user_id' => $user->id]);
        $employment->fill($payload);
        $employment->modified_by = 'MPIP API';
        $employment->save();
    }

    private function syncEducation(User $user, ?array $records): void
    {
        if ($records === null) {
            return;
        }

        // Delete existing education records and replace with MPIP data
        $user->educations()->delete();

        foreach ($records as $edu) {
            $record = new UserEducation([
                'user_id'         => $user->id,
                'school_name'     => $edu['school_name'],
                'date_graduated'  => $edu['date_graduated']  ?? null,
                'degree'          => $edu['degree']          ?? null,
                'education_level' => $edu['education_level'] ?? null,
                'modified_by'     => 'MPIP API',
            ]);
            $record->save();
        }
    }

    private function syncAllotees(User $user, ?array $records): void
    {
        if ($records === null) {
            return;
        }

        // Detach all existing allotees before re-syncing
        DB::table('crew_allotees')
            ->where('user_id', $user->id)
            ->update(['deleted_at' => now()]);

        $primaryAlloteeId = null;

        foreach ($records as $alloteeData) {
            // Upsert allotee by email (most reliable), then name+relationship
            $match = $alloteeData['email']
                ? ['email' => $alloteeData['email']]
                : ['name' => $alloteeData['name'], 'relationship' => $alloteeData['relationship'] ?? null];

            $allotee = Allotee::firstOrNew($match);
            $allotee->fill(array_filter([
                'name'          => $alloteeData['name'],
                'relationship'  => $alloteeData['relationship']  ?? null,
                'mobile_number' => $alloteeData['mobile_number'] ?? null,
                'email'         => $alloteeData['email']         ?? null,
                'address'       => $alloteeData['address']       ?? null,
                'date_of_birth' => $alloteeData['date_of_birth'] ?? null,
                'gender'        => $alloteeData['gender']        ?? null,
            ], fn($v) => $v !== null));
            $allotee->modified_by = 'MPIP API';
            $allotee->save();

            $isPrimary = (bool) ($alloteeData['is_primary'] ?? false);
            $isEmergency = (bool) ($alloteeData['is_emergency_contact'] ?? false);

            // Re-attach (restore or insert) in crew_allotees
            $existing = DB::table('crew_allotees')
                ->where('user_id', $user->id)
                ->where('allotee_id', $allotee->id)
                ->first();

            if ($existing) {
                DB::table('crew_allotees')
                    ->where('user_id', $user->id)
                    ->where('allotee_id', $allotee->id)
                    ->update([
                        'is_primary'           => $isPrimary,
                        'is_emergency_contact' => $isEmergency,
                        'deleted_at'           => null,
                        'updated_at'           => now(),
                    ]);
            } else {
                DB::table('crew_allotees')->insert([
                    'user_id'              => $user->id,
                    'allotee_id'           => $allotee->id,
                    'is_primary'           => $isPrimary,
                    'is_emergency_contact' => $isEmergency,
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ]);
            }

            if ($isPrimary) {
                $primaryAlloteeId = $allotee->id;
            }
        }

        // Update primary allotee on employment record
        if ($primaryAlloteeId && $user->employment) {
            $user->employment->primary_allotee_id = $primaryAlloteeId;
            $user->employment->save();
        }
    }

    private function syncTravelDocuments(string $crewId, ?array $records): void
    {
        if ($records === null) {
            return;
        }

        foreach ($records as $doc) {
            $typeId = $this->travelDocTypes[$doc['document_type_name']]?->id ?? null;

            if (! $typeId) {
                continue; // skip unknown document types
            }

            $record = TravelDocument::firstOrNew([
                'crew_id'                  => $crewId,
                'id_no'                    => $doc['id_no'],
                'travel_document_type_id'  => $typeId,
            ]);

            $record->fill(array_filter([
                'place_of_issue'   => $doc['place_of_issue']   ?? null,
                'date_of_issue'    => $doc['date_of_issue']    ?? null,
                'expiration_date'  => $doc['expiration_date']  ?? null,
                'remaining_pages'  => $doc['remaining_pages']  ?? null,
                'is_US_VISA'       => $doc['is_US_VISA']       ?? null,
                'visa_type'        => $doc['visa_type']        ?? null,
                'file_path'        => $doc['file_path']        ?? null,
                'file_ext'         => $doc['file_ext']         ?? null,
            ], fn($v) => $v !== null));

            $record->modified_by = 'MPIP API';
            $record->save();
        }
    }

    private function syncEmploymentDocuments(string $crewId, ?array $records): void
    {
        if ($records === null) {
            return;
        }

        foreach ($records as $doc) {
            $typeId = $this->employmentDocTypes[$doc['document_type_name']]?->id ?? null;

            if (! $typeId) {
                continue;
            }

            $record = EmploymentDocument::firstOrNew([
                'crew_id'                      => $crewId,
                'employment_document_type_id'  => $typeId,
                'document_number'              => $doc['document_number'] ?? null,
            ]);

            $record->fill(array_filter([
                'file_path' => $doc['file_path'] ?? null,
                'file_ext'  => $doc['file_ext']  ?? null,
            ], fn($v) => $v !== null));

            $record->modified_by = 'MPIP API';
            $record->save();
        }
    }

    private function syncCertificates(string $crewId, ?array $records): void
    {
        if ($records === null) {
            return;
        }

        foreach ($records as $cert) {
            $certificateId = $this->certificatesByCode[$cert['certificate_code']]?->id ?? null;

            if (! $certificateId) {
                continue;
            }

            // Upsert by crew_id + certificate_id + certificate_no
            $record = CrewCertificate::firstOrNew([
                'crew_id'        => $crewId,
                'certificate_id' => $certificateId,
                'certificate_no' => $cert['certificate_no'] ?? null,
            ]);

            $record->fill(array_filter([
                'grade'          => $cert['grade']          ?? null,
                'rank_permitted' => $cert['rank_permitted'] ?? null,
                'issued_by'      => $cert['issued_by']      ?? null,
                'date_issued'    => $cert['date_issued']    ?? null,
                'expiry_date'    => $cert['expiry_date']    ?? null,
                'file_path'      => $cert['file_path']      ?? null,
                'file_ext'       => $cert['file_ext']       ?? null,
            ], fn($v) => $v !== null));

            $record->modified_by = 'MPIP API';
            $record->save();
        }
    }

    private function syncPrograms(User $user, ?array $records): void
    {
        if ($records === null) {
            return;
        }

        foreach ($records as $prog) {
            $programId = $this->programsByName[$prog['program_name']]?->id ?? null;

            if (! $programId) {
                continue;
            }

            $record = UserProgramEmployment::firstOrNew([
                'user_id'    => $user->id,
                'program_id' => $programId,
            ]);

            if (isset($prog['batch'])) {
                $record->batch = $prog['batch'];
            }

            $record->modified_by = 'MPIP API';
            $record->save();
        }
    }
}
