<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use App\Models\DebriefingForm;
use App\Services\DebriefingPdfService;
use App\Mail\DebriefingFormConfirmedMail;
use App\Jobs\GenerateDebriefingPdfJob;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;

class AdminDebriefingFormController extends Controller
{
    /**
     * List all forms with filters + pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        if ($user->is_crew) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $query = DebriefingForm::query();

        // If you want "manning sees all", remove this filter.
        if ($user->department_id) {
            $query->where(function ($q) use ($user) {
                $q->whereNull('department_id')
                  ->orWhere('department_id', $user->department_id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('crew_name')) {
            $query->where('crew_name', 'like', '%' . $request->string('crew_name') . '%');
        }

        if ($request->filled('vessel')) {
            $query->where('embarkation_vessel_name', 'like', '%' . $request->string('vessel') . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('submitted_at', '>=', $request->string('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('submitted_at', '<=', $request->string('date_to'));
        }

        $forms = $query
            ->orderByDesc('submitted_at')
            ->orderByDesc('created_at')
            ->paginate((int) $request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $forms,
        ]);
    }

    /**
     * View a single form (read-only).
     */
    public function show(int $id): JsonResponse
    {
        $user = Auth::user();

        if ($user->is_crew) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $form = DebriefingForm::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $form,
        ]);
    }

    /**
     * Confirm submitted form.
     */
    public function confirm(Request $request, int $id): JsonResponse
    {
        $user = Auth::user();

        if ($user->is_crew) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $form = DebriefingForm::findOrFail($id);

        if ($form->status !== 'submitted') {
            throw ValidationException::withMessages([
                'status' => ['Only submitted forms can be confirmed.'],
            ]);
        }

        DB::beginTransaction();

        try {
            $form->status = 'confirmed';
            $form->confirmed_at = now();
            $form->confirmed_by = $user->id;
            $form->department_id = 11;
            $form->pdf_path = null;
            $form->pdf_generated_at = null;
            $form->pdf_status = 'pending';
            $form->pdf_error = null;

            $form->save();

            DB::commit();

            GenerateDebriefingPdfJob::dispatch($form->id);

            return response()->json([
                'success' => true,
                'data' => $form->fresh(),
                'message' => 'Debriefing form confirmed. PDF is being generated.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm form.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function previewPdf(int $id)
    {
        $user = Auth::user();
        if ($user->is_crew) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $form = DebriefingForm::with('crew')->findOrFail($id);

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
                'message' => 'PDF file is missing. Please regenerate.',
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
        if ($user->is_crew) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $form = DebriefingForm::with('crew')->findOrFail($id);

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
                'message' => 'PDF file is missing. Please regenerate.',
            ], 404);
        }

        $fullPath = Storage::disk('local')->path($form->pdf_path);

        return response()->download(
            $fullPath,
            'debriefing_form_'.$form->id.'.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    public function regeneratePdf(int $id): JsonResponse
    {
        $user = Auth::user();
        if ($user->is_crew) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $form = DebriefingForm::findOrFail($id);

        if ($form->status !== 'confirmed') {
            throw ValidationException::withMessages([
                'status' => ['Only confirmed forms can regenerate PDF.'],
            ]);
        }

        $form->pdf_status = 'pending';
        $form->pdf_error = null;
        $form->pdf_path = null;
        $form->pdf_generated_at = null;
        $form->save();

        GenerateDebriefingPdfJob::dispatch($form->id);

        return response()->json([
            'success' => true,
            'message' => 'PDF regeneration queued.',
            'data' => $form->fresh(),
        ]);
    }

    private function adminOverrideRules(Request $request): array
    {
        return array_merge($this->submitRules($request), [
            'override_reason' => ['nullable', 'string', 'max:500'],
            'confirm_now' => ['nullable', 'boolean'],
        ]);
    }

    public function override(Request $request, int $id): JsonResponse
    {
        $user = Auth::user();
        if ($user->is_crew) return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);

        $form = DebriefingForm::findOrFail($id);

        // allow override when submitted
        if (!in_array($form->status, ['submitted', 'draft'], true)) {
            throw ValidationException::withMessages([
                'status' => ['Only draft/submitted forms can be overridden.'],
            ]);
        }

        $validated = $request->validate($this->adminOverrideRules($request));

        DB::beginTransaction();

        try {
            $form->fill($validated);

            // if admin wants to confirm immediately
            if ($request->boolean('confirm_now')) {
                $form->status = 'confirmed';
                $form->confirmed_at = now();
                $form->confirmed_by = $user->id;

                $form->pdf_path = null;
                $form->pdf_generated_at = null;
                $form->pdf_status = 'pending';
                $form->pdf_error = null;
            }

            $form->save();

            DB::commit();

            if ($request->boolean('confirm_now')) {
                GenerateDebriefingPdfJob::dispatch($form->id);
            }

            return response()->json([
                'success' => true,
                'data' => $form->fresh(),
                'message' => $request->boolean('confirm_now')
                    ? 'Form overridden and confirmed. PDF queued.'
                    : 'Form overridden successfully.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Override failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function submitRules(Request $request): array
    {
        return [
            // Crew Details
            'rank' => ['nullable', 'string', 'max:255'],
            'crew_name' => ['nullable', 'string', 'max:255'],
            'vessel_type' => ['nullable', 'string', 'max:255'],
            'principal_name' => ['nullable', 'string', 'max:255'],

            // Embarkation
            'embarkation_vessel_name' => ['nullable', 'string', 'max:255'],
            'embarkation_place' => ['nullable', 'string', 'max:255'],
            'embarkation_date' => ['nullable', 'date'],

            // Disembarkation
            'disembarkation_date' => ['nullable', 'date'],
            'disembarkation_place' => ['nullable', 'string', 'max:255'],
            'manila_arrival_date' => ['nullable', 'date'],

            // Personal Info
            'present_address' => ['nullable', 'string'],
            'provincial_address' => ['nullable', 'string'],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'date_of_availability' => ['nullable', 'date'],
            'availability_status' => ['nullable', 'string', 'max:255'],
            'next_vessel_assignment_date' => ['nullable', 'date'],
            'long_vacation_reason' => ['nullable', 'string'],

            // Medical
            'has_illness_or_injury' => ['nullable', 'boolean'],
            'illness_injury_types' => ['nullable', 'array'],
            'illness_injury_types.*' => ['string', 'max:255'],
            'lost_work_days' => ['nullable', 'integer', 'min:0'],
            'medical_incident_details' => ['nullable', 'string'],

            // Comments & Feedback
            'comment_q1_technical' => ['nullable', 'string'],
            'comment_q2_crewing' => ['nullable', 'string'],
            'comment_q3_complaint' => ['nullable', 'string'],
            'comment_q4_immigrant_visa' => ['nullable', 'string'],
            'comment_q5_commitments' => ['nullable', 'string'],
            'comment_q6_additional' => ['nullable', 'string'],

            // Signature (if admin is allowed to override signature, keep this)
            'signature_path' => ['nullable', 'string', 'max:2048'],
        ];
    }
}
