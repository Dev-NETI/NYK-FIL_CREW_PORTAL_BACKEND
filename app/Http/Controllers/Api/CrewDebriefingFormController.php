<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DebriefingForm;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use App\Mail\DebriefingFormSubmittedToCrewMail;
use App\Mail\DebriefingFormSubmittedToDepartmentMail;

class CrewDebriefingFormController extends Controller
{
    /**
     * List all forms of the authenticated crew.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (! $user->is_crew) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $forms = DebriefingForm::query()
            ->where('crew_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate((int) $request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $forms,
        ]);
    }

    /**
     * Create a new draft form.
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (! $user->is_crew) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate($this->draftRules());

        $user->load([
            'profile',
            'employment.rank',
            'contracts.vessel.vesselType',
            'userContact.currentAddress',
            'userContact.permanentAddress',
        ]);

        $profile = $user->profile;
        $contact = $user->userContact;

        $rankName = $user->employment?->rank?->name;

        $contract = method_exists($user, 'currentContract')
            ? ($user->currentContract() ?? $user->contracts()->latest('contract_start_date')->first())
            : $user->contracts()->latest('contract_start_date')->first();

        $vessel = $contract?->vessel;

        $vesselTypeName = $vessel?->vesselType?->name;
        $principalName = $vessel?->name;

        $presentAddress = $contact?->currentAddress
            ? $this->formatAddress($contact->currentAddress)
            : null;

        $provincialAddress = $contact?->permanentAddress
            ? $this->formatAddress($contact->permanentAddress)
            : null;

        $form = DebriefingForm::create(array_merge($validated, [
            'crew_id' => $user->id,
            'status' => 'draft',

            // Crew details (server-owned)
            'rank' => $rankName ?? ($profile->rank ?? $profile->rank_name ?? null),
            'crew_name' => $profile
                ? $this->formatCrewName($profile)
                : ($user->name ?? $user->email),
            'vessel_type' => $vesselTypeName,
            'principal_name' => $principalName,

            // Personal info (prefill only if not provided)
            'email' => $validated['email'] ?? $user->email,
            'phone_number' => $validated['phone_number']
                ?? ($contact?->mobile_number ?? ($profile->phone_number ?? null)),
            'present_address' => $validated['present_address'] ?? $presentAddress,
            'provincial_address' => $validated['provincial_address'] ?? $provincialAddress,
        ]));

        return response()->json([
            'success' => true,
            'data' => $form,
            'message' => 'Debriefing form draft created.',
        ], 201);
    }

    /**
     * Show a single form.
     */
    public function show(int $id): JsonResponse
    {
        $user = Auth::user();

        if (! $user->is_crew) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $form = DebriefingForm::where('id', $id)
            ->where('crew_id', $user->id)
            ->with([
                'crew.userContact.currentAddress',
                'crew.userContact.permanentAddress',
            ])
            ->firstOrFail();

        $contact = $form->crew?->userContact;

        $prefill = [
            'mobile_number' => $contact?->mobile_number,
            'email' => $form->crew?->email,
            'present_address' => $contact?->currentAddress
                ? $this->formatAddress($contact->currentAddress)
                : null,
            'provincial_address' => $contact?->permanentAddress
                ? $this->formatAddress($contact->permanentAddress)
                : null,
        ];

        return response()->json([
            'success' => true,
            'data' => $form,
            'prefill' => $prefill,
        ]);
    }

    /**
     * Update draft form only.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = Auth::user();

        if (! $user->is_crew) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $form = DebriefingForm::where('id', $id)
            ->where('crew_id', $user->id)
            ->firstOrFail();

        if ($form->status !== 'draft') {
            throw ValidationException::withMessages([
                'status' => ['Only draft forms can be edited.'],
            ]);
        }

        $validated = $request->validate($this->draftRules());

        unset($validated['rank'], $validated['crew_name']);

        $form->fill($validated);
        $form->save();

        return response()->json([
            'success' => true,
            'data' => $form->fresh(),
            'message' => 'Debriefing form draft updated.',
        ]);
    }

    /**
     * Submit draft | locks the form.
     */
   public function submit(Request $request, int $id): JsonResponse
    {
        $user = Auth::user();

        if (! $user->is_crew) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $form = DebriefingForm::where('id', $id)
            ->where('crew_id', $user->id)
            ->firstOrFail();

        if ($form->status !== 'draft') {
            throw ValidationException::withMessages([
                'status' => ['Only draft forms can be submitted.'],
            ]);
        }

        $validated = $request->validate($this->submitRules($request));

        $form->fill($validated);
        $form->status = 'submitted';
        $form->submitted_at = now();

        if (! $form->department_id && $user->department_id) {
            $form->department_id = $user->department_id;
        }

        $form->save();

        if ($user->email) {
            Mail::to($user->email)->send(new DebriefingFormSubmittedToCrewMail($form, $user));
        }

        if ($form->department_id) {
            $department = Department::find($form->department_id);

            if ($department && $department->email) {
                Mail::to($department->email)->send(
                    new DebriefingFormSubmittedToDepartmentMail($form, $user, $department)
                );
            }
        }

        return response()->json([
            'success' => true,
            'data' => $form->fresh(),
            'message' => 'Debriefing form submitted successfully.', 
        ]);
    }

    private function formatCrewName($profile): string
    {
        $first = trim((string) ($profile->first_name ?? ''));
        $middle = trim((string) ($profile->middle_name ?? ''));
        $last = trim((string) ($profile->last_name ?? ''));

        $middlePart = $middle ? " {$middle}" : '';

        return trim("{$last}, {$first}{$middlePart}");
    }

    private function formatAddress($address): string
    {
        if (! empty($address->full_address)) {
            return $address->full_address;
        }

        $parts = array_filter([
            $address->street_address ?? null,
            optional($address->city)->name ?? null,
            optional($address->province)->name ?? null,
            optional($address->region)->name ?? null,
            $address->zip_code ?? null,
        ]);

        return implode(', ', $parts);
    }

     public function previewPdf(int $id)
    {
        $user = Auth::user();

        if (!$user->is_crew) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $form = DebriefingForm::where('id', $id)
            ->where('crew_id', $user->id)
            ->firstOrFail();

        if ($form->status !== 'confirmed') {
            throw ValidationException::withMessages([
                'status' => ['Only confirmed forms can be previewed.'],
            ]);
        }

        if ($form->pdf_status !== 'ready' || !$form->pdf_path) {
            return response()->json([
                'success' => false,
                'message' => 'PDF is still being generated. Please try again later.',
                'pdf_status' => $form->pdf_status,
            ], 409);
        }

        if (!Storage::disk('local')->exists($form->pdf_path)) {
            return response()->json([
                'success' => false,
                'message' => 'PDF file is missing. Please contact support or retry later.',
            ], 404);
        }

        $fullPath = Storage::disk('local')->path($form->pdf_path);

        return response()->file($fullPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="debriefing_form_'.$form->id.'.pdf"',
        ]);
    }

    public function downloadPdf(int $id)
    {
        $user = Auth::user();

        if (!$user->is_crew) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $form = DebriefingForm::where('id', $id)
            ->where('crew_id', $user->id)
            ->firstOrFail();

        if ($form->status !== 'confirmed') {
            throw ValidationException::withMessages([
                'status' => ['Only confirmed forms can be downloaded.'],
            ]);
        }

        if ($form->pdf_status !== 'ready' || !$form->pdf_path) {
            return response()->json([
                'success' => false,
                'message' => 'PDF is still being generated. Please try again later.',
                'pdf_status' => $form->pdf_status,
            ], 409);
        }

        if (!Storage::disk('local')->exists($form->pdf_path)) {
            return response()->json([
                'success' => false,
                'message' => 'PDF file is missing. Please contact support or retry later.',
            ], 404);
        }

        $fullPath = Storage::disk('local')->path($form->pdf_path);

        return response()->download(
            $fullPath,
            'debriefing_form_'.$form->id.'.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    private function draftRules(): array
    {
        return [
            'embarkation_vessel_name' => 'sometimes|nullable|string|max:255',
            'embarkation_place' => 'sometimes|nullable|string|max:255',
            'embarkation_date' => 'sometimes|nullable|date',
            'disembarkation_date' => 'sometimes|nullable|date',
            'disembarkation_place' => 'sometimes|nullable|string|max:255',
            'manila_arrival_date' => 'sometimes|nullable|date',

            'present_address' => 'sometimes|nullable|string',
            'provincial_address' => 'sometimes|nullable|string',
            'phone_number' => 'sometimes|nullable|string|max:50',
            'email' => 'sometimes|nullable|email|max:255',
            'date_of_availability' => 'sometimes|nullable|date',
            'availability_status' => 'sometimes|nullable|string|max:100',
            'next_vessel_assignment_date' => 'sometimes|nullable|date',
            'long_vacation_reason' => 'sometimes|nullable|string',

            'has_illness_or_injury' => 'sometimes|nullable|boolean',
            'illness_injury_types' => 'sometimes|nullable|array',
            'illness_injury_types.*' => 'string|max:50',
            'lost_work_days' => 'sometimes|nullable|integer|min:0|max:365',
            'medical_incident_details' => 'sometimes|nullable|string',

            'comment_q1_technical' => 'sometimes|nullable|string',
            'comment_q2_crewing' => 'sometimes|nullable|string',
            'comment_q3_complaint' => 'sometimes|nullable|string',
            'comment_q4_immigrant_visa' => 'sometimes|nullable|string',
            'comment_q5_commitments' => 'sometimes|nullable|string',
            'comment_q6_additional' => 'sometimes|nullable|string',
        ];
    }

    private function submitRules(Request $request): array
    {
        $rules = [
            'embarkation_vessel_name' => 'required|string|max:255',
            'embarkation_place' => 'required|string|max:255',
            'embarkation_date' => 'required|date',

            'disembarkation_date' => 'required|date',
            'disembarkation_place' => 'required|string|max:255',
            'manila_arrival_date' => 'required|date',

            'present_address' => 'required|string',
            'provincial_address' => 'required|string',
            'phone_number' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'date_of_availability' => 'required|date',
            'availability_status' => 'required|string|max:100',
            'next_vessel_assignment_date' => 'nullable|date',
            'long_vacation_reason' => 'nullable|string',

            'has_illness_or_injury' => 'required|boolean',
            'illness_injury_types' => 'nullable|array',
            'illness_injury_types.*' => 'string|max:50',
            'lost_work_days' => 'nullable|integer|min:0|max:365',
            'medical_incident_details' => 'nullable|string',

            'comment_q1_technical' => 'nullable|string',
            'comment_q2_crewing' => 'nullable|string',
            'comment_q3_complaint' => 'nullable|string',
            'comment_q4_immigrant_visa' => 'nullable|string',
            'comment_q5_commitments' => 'nullable|string',
            'comment_q6_additional' => 'nullable|string',
        ];

        if ($request->boolean('has_illness_or_injury')) {
            $rules['medical_incident_details'] = 'required|string';
        }

        return $rules;
    }
}
