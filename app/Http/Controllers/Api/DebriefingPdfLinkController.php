<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DebriefingForm;
use App\Services\DebriefingPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DebriefingPdfLinkController extends Controller
{
    /**
     * Signed URL download (no auth required, but signature + expiry required).
     */
    public function download(Request $request, int $id)
    {
        $form = DebriefingForm::findOrFail($id);

        if ($form->status !== 'confirmed') {
            throw ValidationException::withMessages([
                'status' => ['Only confirmed forms can be downloaded.'],
            ]);
        }

        if (! $request->hasValidSignature()) {
            return response()->json(['success' => false, 'message' => 'Link expired or invalid.'], 403);
        }

        $crewId = (int) $request->query('crew_id');
        if ($crewId !== (int) $form->crew_id) {
            return response()->json(['success' => false, 'message' => 'Invalid link.'], 403);
        }

        $pdfPath = $form->pdf_path;

        if (! $pdfPath || !Storage::disk('local')->exists($pdfPath)) {
            return response()->json([
                'success' => false,
                'message' => 'PDF is not ready yet. Please try again later.',
            ], 409);
        }

        return response()->download(
            Storage::disk('local')->path($pdfPath),
            'debriefing_form_'.$form->id.'.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

}
