<?php

namespace App\Jobs;

use App\Mail\DebriefingFormConfirmedMail;
use App\Models\DebriefingForm;
use App\Services\DebriefingPdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class GenerateDebriefingPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 180;
    public int $tries = 3;

    public function __construct(public int $formId) {}

    public function handle(DebriefingPdfService $pdfService): void
    {
        $form = DebriefingForm::query()->with('crew')->find($this->formId);
        if (! $form) return;

        if ($form->status !== 'confirmed') return;

        if ($form->pdf_emailed_at) return;

        $form->pdf_status = 'generating';
        $form->pdf_error = null;
        $form->save();

        try {
            $pdfService->generateConfirmedFormPdf($form);

            $form->refresh()->load('crew');
            if (! $form->pdf_path || ! Storage::disk('local')->exists($form->pdf_path)) {
                throw new \RuntimeException('PDF generation finished but file not found.');
            }

            $form->pdf_status = 'ready';
            $form->pdf_error = null;
            $form->save();

            if (! $form->crew?->email) {
                Log::warning('Crew email missing; cannot send debriefing PDF email', [
                    'form_id' => $form->id,
                    'crew_id' => $form->crew_id,
                ]);
                return;
            }

            // Build signed download link (valid for 7 days | temporary) 
            $downloadUrl = URL::temporarySignedRoute(
                'debriefing.pdf.download',
                now()->addDays(7),
                ['id' => $form->id, 'crew_id' => $form->crew_id]
            );

            $claimed = DebriefingForm::query()
                ->whereKey($form->id)
                ->whereNull('pdf_emailed_at')
                ->update(['pdf_emailed_at' => now()]);

            if (! $claimed) {
                return;
            }

            Mail::to($form->crew->email)->send(
                new DebriefingFormConfirmedMail($form, $form->crew, $downloadUrl)
            );
        } catch (\Throwable $e) {
            Log::error('Debriefing PDF generation/email failed', [
                'form_id' => $this->formId,
                'error' => $e->getMessage(),
            ]);

            $form->refresh();
            $form->pdf_status = 'failed';
            $form->pdf_error = $e->getMessage();
            $form->save();

            throw $e;
        }
    }
}
