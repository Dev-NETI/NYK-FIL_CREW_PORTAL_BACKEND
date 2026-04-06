<?php

namespace App\Http\Controllers\Api\Mpip;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Allotee;
use App\Models\Fleet;
use App\Models\Program;
use App\Models\Rank;
use App\Models\User;
use App\Models\UserContact;
use App\Models\UserEducation;
use App\Models\UserEmployment;
use App\Models\UserPhysicalTrait;
use App\Models\UserProfile;
use App\Models\UserProgramEmployment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MpipCrewUpdateController extends Controller
{
    // ─── Lookup caches (populated once per request) ──────────────────────────
    private array $ranksByCode     = [];
    private array $fleetsByName    = [];
    private array $companiesByName = [];
    private array $programsByName  = [];

    // ─── Validation rules shared across all 3 endpoints ──────────────────────
    private array $validationRules = [
        // T1 — Basic Info
        'basic_info'               => 'nullable|array',
        'basic_info.first_name'    => 'nullable|string|max:255',
        'basic_info.middle_name'   => 'nullable|string|max:255',
        'basic_info.last_name'     => 'nullable|string|max:255',
        'basic_info.suffix'        => 'nullable|string|max:50',
        'basic_info.birth_date'    => 'nullable|date',
        'basic_info.birth_place'   => 'nullable|string|max:255',
        'basic_info.gender'        => 'nullable|string|in:male,female',
        'basic_info.nationality'   => 'nullable|string|max:100',
        'basic_info.civil_status'  => 'nullable|string|in:single,married,widowed,separated',
        'basic_info.religion'      => 'nullable|string|max:100',
        'basic_info.blood_type'    => 'nullable|string|max:10',
        'basic_info.rank_code'     => 'nullable|string|max:50',
        'basic_info.fleet_name'    => 'nullable|string|max:255',
        'basic_info.company_name'  => 'nullable|string|max:255',
        'basic_info.is_industrial' => 'nullable|boolean',

        // T2 — Contact
        'contact'                                => 'nullable|array',
        'contact.mobile_number'                  => 'nullable|string|max:50',
        'contact.alternate_phone'                => 'nullable|string|max:50',
        'contact.emergency_contact_name'         => 'nullable|string|max:255',
        'contact.emergency_contact_phone'        => 'nullable|string|max:50',
        'contact.emergency_contact_relationship' => 'nullable|string|max:100',

        // T3 — Addresses
        'addresses'                  => 'nullable|array',
        'addresses.*.type'           => 'required_with:addresses|string|in:permanent,current',
        'addresses.*.street_address' => 'nullable|string|max:255',
        'addresses.*.barangay'       => 'nullable|string|max:255',
        'addresses.*.city'           => 'nullable|string|max:255',
        'addresses.*.province'       => 'nullable|string|max:255',
        'addresses.*.region'         => 'nullable|string|max:255',
        'addresses.*.zip_code'       => 'nullable|string|max:20',
        'addresses.*.full_address'   => 'nullable|string|max:500',

        // T4 — Employment
        'employment'                    => 'nullable|array',
        'employment.rank_code'          => 'nullable|string|max:50',
        'employment.fleet_name'         => 'nullable|string|max:255',
        'employment.crew_status'        => 'nullable|string|in:on_board,on_vacation,standby,resigned,terminated',
        'employment.hire_status'        => 'nullable|string|in:new_hire,re_hire,promoted,transferred',
        'employment.hire_date'          => 'nullable|date',
        'employment.passport_number'    => 'nullable|string|max:100',
        'employment.passport_expiry'    => 'nullable|date',
        'employment.seaman_book_number' => 'nullable|string|max:100',
        'employment.seaman_book_expiry' => 'nullable|date',
        'employment.basic_salary'       => 'nullable|numeric|min:0',
        'employment.employment_notes'   => 'nullable|string',

        // T5 — Education
        'education'                   => 'nullable|array',
        'education.*.school_name'     => 'required_with:education|string|max:255',
        'education.*.degree'          => 'nullable|string|max:255',
        'education.*.education_level' => 'nullable|string|in:high_school,college,vocational,post_graduate,higher_educational',
        'education.*.date_graduated'  => 'nullable|date',

        // T6 — Physical Traits
        'physical_traits'            => 'nullable|array',
        'physical_traits.height_cm'  => 'nullable|numeric|min:0',
        'physical_traits.weight_kg'  => 'nullable|numeric|min:0',
        'physical_traits.blood_type' => 'nullable|string|max:10',
        'physical_traits.eye_color'  => 'nullable|string|max:50',
        'physical_traits.hair_color' => 'nullable|string|max:50',

        // T7 — Allotees
        'allotees'                        => 'nullable|array',
        'allotees.*.name'                 => 'required_with:allotees|string|max:255',
        'allotees.*.relationship'         => 'required_with:allotees|string|max:100',
        'allotees.*.mobile_number'        => 'nullable|string|max:50',
        'allotees.*.email'                => 'nullable|email',
        'allotees.*.date_of_birth'        => 'nullable|date',
        'allotees.*.gender'               => 'nullable|string|in:male,female',
        'allotees.*.is_primary'           => 'nullable|boolean',
        'allotees.*.is_emergency_contact' => 'nullable|boolean',

        // T12 — Programs
        'programs'               => 'nullable|array',
        'programs.*.program_name' => 'required_with:programs|string|max:255',
        'programs.*.batch'        => 'nullable|string|max:100',
    ];

    // ─── Public route handlers ────────────────────────────────────────────────

    /**
     * Create or update cruise crew (users.is_industrial = 0).
     *
     * PUT /api/mpip/update/cruise/{crew_id}
     */
    public function updateCruise(Request $request, string $crew_id): JsonResponse
    {
        return $this->performUpdate($crew_id, $request, 'cruise');
    }

    /**
     * Create or update non-NYK-affiliated crew (user_profiles.company_id != 1).
     *
     * PUT /api/mpip/update/non-nyk/{crew_id}
     */
    public function updateNonNyk(Request $request, string $crew_id): JsonResponse
    {
        return $this->performUpdate($crew_id, $request, 'non_nyk');
    }

    /**
     * Create or update industrial crew (users.is_industrial = 1).
     *
     * PUT /api/mpip/update/industrial/{crew_id}
     */
    public function updateIndustrial(Request $request, string $crew_id): JsonResponse
    {
        return $this->performUpdate($crew_id, $request, 'industrial');
    }

    // ─── Core handler ─────────────────────────────────────────────────────────

    private function performUpdate(string $crewId, Request $request, string $conditionType): JsonResponse
    {
        $profile = UserProfile::where('crew_id', $crewId)->first();
        $action  = $profile ? 'updated' : 'created';
        $user    = null;

        if ($action === 'created') {
            // Email is required to create a new crew record
            if (! $request->has('basic_info') || empty($request->input('basic_info.email'))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Crew member not found. To create a new record, provide basic_info.email in the payload.',
                    'error'   => "No crew record exists with crew_id: {$crewId}",
                ], 422);
            }

            $email = $request->input('basic_info.email');

            if (User::where('email', $email)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email already registered.',
                    'error'   => "A user with email '{$email}' already exists.",
                ], 422);
            }

            $rules = array_merge($this->validationRules, [
                'basic_info.email' => 'required|email',
            ]);
        } else {
            $user = User::find($profile->user_id);

            if (! $user || ! $user->is_crew) {
                return response()->json([
                    'success' => false,
                    'message' => 'Crew member not found.',
                    'error'   => "No active crew account linked to crew_id: {$crewId}",
                ], 404);
            }

            $conditionError = $this->checkCondition($user, $profile, $conditionType);
            if ($conditionError) {
                return response()->json([
                    'success' => false,
                    'message' => 'Crew type mismatch.',
                    'error'   => $conditionError,
                ], 422);
            }

            $rules = $this->validationRules;
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 400);
        }

        $this->loadLookupCaches();

        $updatedSections = [];

        DB::transaction(function () use ($crewId, $conditionType, $action, &$user, &$profile, $request, &$updatedSections) {
            if ($action === 'created') {
                $isIndustrial = match ($conditionType) {
                    'cruise'     => 0,
                    'industrial' => 1,
                    default      => (int) $request->boolean('basic_info.is_industrial'),
                };

                $user              = new User();
                $user->email       = $request->input('basic_info.email');
                $user->is_crew     = true;
                $user->is_industrial = $isIndustrial;
                $user->modified_by = 'MPIP API';
                $user->save();

                $profile              = new UserProfile();
                $profile->user_id     = $user->id;
                $profile->crew_id     = $crewId;
                $profile->modified_by = 'MPIP API';
                $profile->save();
            }

            // T1 — Basic Info
            if ($request->has('basic_info')) {
                $basicInfo = $request->input('basic_info') ?? [];
                // On create, is_industrial was already set from the endpoint — skip override
                if ($action === 'created' && $conditionType !== 'non_nyk') {
                    unset($basicInfo['is_industrial']);
                }
                $this->syncBasicInfo($user, $profile, $basicInfo);
                $updatedSections[] = 'basic_info';
            }

            // T2 + T3 — Contact & Addresses
            $hasContact   = $request->has('contact');
            $hasAddresses = $request->has('addresses');

            if ($hasContact || $hasAddresses) {
                $this->syncContactAndAddresses(
                    $user,
                    $hasContact   ? ($request->input('contact')   ?? []) : null,
                    $hasAddresses ? ($request->input('addresses') ?? []) : null,
                );
                if ($hasContact)   $updatedSections[] = 'contact';
                if ($hasAddresses) $updatedSections[] = 'addresses';
            }

            // T4 — Employment
            if ($request->has('employment')) {
                $this->syncEmployment($user, $request->input('employment') ?? []);
                $updatedSections[] = 'employment';
            }

            // T5 — Education
            if ($request->has('education')) {
                $this->syncEducation($user, $request->input('education') ?? []);
                $updatedSections[] = 'education';
            }

            // T6 — Physical Traits
            if ($request->has('physical_traits')) {
                $this->syncPhysicalTraits($user, $request->input('physical_traits') ?? []);
                $updatedSections[] = 'physical_traits';
            }

            // T7 — Allotees
            if ($request->has('allotees')) {
                $this->syncAllotees($user, $request->input('allotees') ?? []);
                $updatedSections[] = 'allotees';
            }

            // T12 — Programs
            if ($request->has('programs')) {
                $this->syncPrograms($user, $request->input('programs') ?? []);
                $updatedSections[] = 'programs';
            }
        });

        $message = $action === 'created'
            ? 'Crew record created successfully.'
            : 'Crew record updated successfully.';

        return response()->json([
            'success'          => true,
            'action'           => $action,
            'message'          => $message,
            'crew_id'          => $crewId,
            'updated_sections' => $updatedSections,
            'timestamp'        => now()->toIso8601String(),
        ]);
    }

    // ─── Condition validator ──────────────────────────────────────────────────

    private function checkCondition(User $user, UserProfile $profile, string $conditionType): ?string
    {
        return match ($conditionType) {
            'cruise' => $user->is_industrial !== 0
                ? "This endpoint is for cruise crew (is_industrial = 0). The crew member has is_industrial = {$user->is_industrial}."
                : null,

            'non_nyk' => $profile->company_id === 1
                ? "This endpoint is for non-NYK crew (company_id ≠ 1). The crew member belongs to NYK-Fil Ship Management, Inc. (company_id = 1)."
                : null,

            'industrial' => $user->is_industrial !== 1
                ? "This endpoint is for industrial crew (is_industrial = 1). The crew member has is_industrial = {$user->is_industrial}."
                : null,

            default => null,
        };
    }

    // ─── Lookup cache loader ──────────────────────────────────────────────────

    private function loadLookupCaches(): void
    {
        $this->ranksByCode     = Rank::whereNotNull('code')->get(['id', 'code'])->keyBy('code')->all();
        $this->fleetsByName    = Fleet::get(['id', 'name'])->keyBy('name')->all();
        $this->companiesByName = \App\Models\Company::get(['id', 'name'])->keyBy('name')->all();
        $this->programsByName  = Program::get(['id', 'name'])->keyBy('name')->all();
    }

    // ─── T1: Basic Info ───────────────────────────────────────────────────────

    private function syncBasicInfo(User $user, UserProfile $profile, array $data): void
    {
        if (array_key_exists('is_industrial', $data)) {
            $user->is_industrial = $data['is_industrial'];
            $user->modified_by   = 'MPIP API';
            $user->save();
        }

        $rankId    = isset($data['rank_code'])    ? ($this->ranksByCode[$data['rank_code']]?->id      ?? null) : null;
        $fleetId   = isset($data['fleet_name'])   ? ($this->fleetsByName[$data['fleet_name']]?->id    ?? null) : null;
        $companyId = isset($data['company_name']) ? ($this->companiesByName[$data['company_name']]?->id ?? null) : null;

        $payload = array_filter([
            'first_name'   => $data['first_name']   ?? null,
            'middle_name'  => $data['middle_name']  ?? null,
            'last_name'    => $data['last_name']    ?? null,
            'suffix'       => $data['suffix']       ?? null,
            'birth_date'   => $data['birth_date']   ?? null,
            'birth_place'  => $data['birth_place']  ?? null,
            'gender'       => $data['gender']       ?? null,
            'nationality'  => $data['nationality']  ?? null,
            'civil_status' => $data['civil_status'] ?? null,
            'religion'     => $data['religion']     ?? null,
            'blood_type'   => $data['blood_type']   ?? null,
        ], fn($v) => $v !== null);

        if ($rankId)    $payload['rank_id']    = $rankId;
        if ($fleetId)   $payload['fleet_id']   = $fleetId;
        if ($companyId) $payload['company_id'] = $companyId;

        if (! empty($payload)) {
            $profile->fill($payload);
            $profile->modified_by = 'MPIP API';
            $profile->save();
        }
    }

    // ─── T2 + T3: Contact & Addresses ────────────────────────────────────────

    private function syncContactAndAddresses(User $user, ?array $contactData, ?array $addressesArray): void
    {
        $contact = $user->contacts ?? new UserContact(['user_id' => $user->id]);

        $permanentAddressId = $contact->permanent_address_id;
        $currentAddressId   = $contact->current_address_id;

        if (! empty($addressesArray)) {
            foreach ($addressesArray as $addrData) {
                $type = $addrData['type'] ?? null;
                if ($type === 'permanent') {
                    $permanentAddressId = $this->upsertAddress($user->id, 'permanent', $addrData);
                } elseif ($type === 'current') {
                    $currentAddressId = $this->upsertAddress($user->id, 'current', $addrData);
                }
            }
        }

        $contactPayload = [];

        if (! empty($contactData)) {
            $contactPayload = array_filter([
                'mobile_number'                  => $contactData['mobile_number']                  ?? null,
                'alternate_phone'                => $contactData['alternate_phone']                ?? null,
                'emergency_contact_name'         => $contactData['emergency_contact_name']         ?? null,
                'emergency_contact_phone'        => $contactData['emergency_contact_phone']        ?? null,
                'emergency_contact_relationship' => $contactData['emergency_contact_relationship'] ?? null,
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
            'barangay'       => $data['barangay']       ?? null,
            'zip_code'       => $data['zip_code']       ?? null,
        ], fn($v) => $v !== null));
        $address->modified_by = 'MPIP API';
        $address->save();

        return $address->id;
    }

    // ─── T4: Employment ───────────────────────────────────────────────────────

    private function syncEmployment(User $user, array $data): void
    {
        if (empty($data)) {
            return;
        }

        $rankId  = isset($data['rank_code'])  ? ($this->ranksByCode[$data['rank_code']]?->id  ?? null) : null;
        $fleetId = isset($data['fleet_name']) ? ($this->fleetsByName[$data['fleet_name']]?->id ?? null) : null;

        $payload = array_filter([
            'crew_status'        => $data['crew_status']        ?? null,
            'hire_status'        => $data['hire_status']        ?? null,
            'hire_date'          => $data['hire_date']          ?? null,
            'passport_number'    => $data['passport_number']    ?? null,
            'passport_expiry'    => $data['passport_expiry']    ?? null,
            'seaman_book_number' => $data['seaman_book_number'] ?? null,
            'seaman_book_expiry' => $data['seaman_book_expiry'] ?? null,
            'basic_salary'       => $data['basic_salary']       ?? null,
            'employment_notes'   => $data['employment_notes']   ?? null,
        ], fn($v) => $v !== null);

        if ($rankId)  $payload['rank_id']  = $rankId;
        if ($fleetId) $payload['fleet_id'] = $fleetId;

        $employment = $user->employment ?? new UserEmployment(['user_id' => $user->id]);
        $employment->fill($payload);
        $employment->modified_by = 'MPIP API';
        $employment->save();
    }

    // ─── T5: Education ────────────────────────────────────────────────────────

    private function syncEducation(User $user, array $records): void
    {
        if (empty($records)) {
            return;
        }

        foreach ($records as $edu) {
            $match = ['user_id' => $user->id, 'school_name' => $edu['school_name']];

            if (! empty($edu['degree'])) {
                $match['degree'] = $edu['degree'];
            }

            $record = UserEducation::firstOrNew($match);
            $record->fill(array_filter([
                'school_name'     => $edu['school_name'],
                'degree'          => $edu['degree']          ?? null,
                'education_level' => $edu['education_level'] ?? null,
                'date_graduated'  => $edu['date_graduated']  ?? null,
            ], fn($v) => $v !== null));
            $record->modified_by = 'MPIP API';
            $record->save();
        }
    }

    // ─── T6: Physical Traits ──────────────────────────────────────────────────

    private function syncPhysicalTraits(User $user, array $data): void
    {
        if (empty($data)) {
            return;
        }

        $payload = array_filter([
            'height'     => $data['height_cm']  ?? null,
            'weight'     => $data['weight_kg']  ?? null,
            'blood_type' => $data['blood_type'] ?? null,
            'eye_color'  => $data['eye_color']  ?? null,
            'hair_color' => $data['hair_color'] ?? null,
        ], fn($v) => $v !== null);

        $traits = UserPhysicalTrait::firstOrNew(['user_id' => $user->id]);
        $traits->fill($payload);
        $traits->modified_by = 'MPIP API';
        $traits->save();
    }

    // ─── T7: Allotees ─────────────────────────────────────────────────────────

    private function syncAllotees(User $user, array $records): void
    {
        if (empty($records)) {
            return;
        }

        $primaryAlloteeId = null;

        foreach ($records as $alloteeData) {
            $match = ! empty($alloteeData['email'])
                ? ['email' => $alloteeData['email']]
                : ['name' => $alloteeData['name'], 'relationship' => $alloteeData['relationship']];

            $allotee = Allotee::firstOrNew($match);
            $allotee->fill(array_filter([
                'name'          => $alloteeData['name'],
                'relationship'  => $alloteeData['relationship']  ?? null,
                'mobile_number' => $alloteeData['mobile_number'] ?? null,
                'email'         => $alloteeData['email']         ?? null,
                'date_of_birth' => $alloteeData['date_of_birth'] ?? null,
                'gender'        => $alloteeData['gender']        ?? null,
            ], fn($v) => $v !== null));
            $allotee->modified_by = 'MPIP API';
            $allotee->save();

            $isPrimary   = (bool) ($alloteeData['is_primary']           ?? false);
            $isEmergency = (bool) ($alloteeData['is_emergency_contact'] ?? false);

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

        if ($primaryAlloteeId && $user->employment) {
            $user->employment->primary_allotee_id = $primaryAlloteeId;
            $user->employment->save();
        }
    }

    // ─── T12: Programs ────────────────────────────────────────────────────────

    private function syncPrograms(User $user, array $records): void
    {
        if (empty($records)) {
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
