<?php

namespace App\Services;

use App\Models\DebriefingForm;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class DebriefingPdfService
{
    /**
     * Generates (or returns existing) PDF path in storage for a confirmed form.
     * Returns storage-relative path e.g. "debriefing_pdfs/debriefing_form_1.pdf"
     */
    public function generateConfirmedFormPdf(DebriefingForm $form): string
    {
        if ($form->status !== 'confirmed') {
            abort(422, 'Only confirmed forms can generate PDF.');
        }

        $pdfRelativePath = "debriefing_pdfs/debriefing_form_{$form->id}.pdf";

        if ($form->pdf_path && Storage::disk('local')->exists($form->pdf_path)) {
            return $form->pdf_path;
        }

        /**
         * Actual file is: storage/app/private/templates/debriefing_template.xlsx
         * Therefore relative to disk root: templates/debriefing_template.xlsx
         */
        $templateRelativePath = 'templates/debriefing_template.xlsx';

        if (!Storage::disk('local')->exists($templateRelativePath)) {
            abort(
                500,
                'Debriefing template not found. Expected: storage/app/private/templates/debriefing_template.xlsx'
            );
        }

        $templateFullPath = Storage::disk('local')->path($templateRelativePath);

        $spreadsheet = IOFactory::load($templateFullPath);

        $sheet = $spreadsheet->getSheetByName('DBF') ?? $spreadsheet->getActiveSheet();

        $this->fillTemplate($sheet, $form);

        $setup = $sheet->getPageSetup();

        $setup->setPaperSize(PageSetup::PAPERSIZE_LEGAL);
        $setup->setOrientation(PageSetup::ORIENTATION_PORTRAIT);

        $lastRow = $sheet->getHighestRow();
        $setup->setPrintArea("A1:N{$lastRow}");

        $setup->setFitToWidth(1);
        $setup->setFitToHeight(0);
        $setup->setScale(null);

        $setup->setHorizontalCentered(true);
        $setup->setVerticalCentered(false);

        $margins = $sheet->getPageMargins();
        $margins->setTop(0.20);
        $margins->setBottom(0.20);
        $margins->setLeft(0.20);
        $margins->setRight(0.20);
        $margins->setHeader(0);
        $margins->setFooter(0);

        Storage::disk('local')->makeDirectory('debriefing_pdfs');

        $pdfFullPath = Storage::disk('local')->path($pdfRelativePath);

        $writer = new Mpdf($spreadsheet);
        $writer->save($pdfFullPath);

        $form->pdf_path = $pdfRelativePath;
        $form->pdf_generated_at = now();
        $form->save();

        return $pdfRelativePath;
    }

    private function fillTemplate($sheet, DebriefingForm $form): void
    {
        $s = fn ($v) => $v === null ? '' : (string) $v;

        $d = function ($date) {
            if (!$date) return '';
            try {
                return \Carbon\Carbon::parse($date)->format('m/d/Y');
            } catch (\Throwable $e) {
                return (string) $date;
            }
        };

        /* 
           Based on template mapping:
           Rank: D10
           Name: D12
           Vessel Type: L10
           Principal: L11
        */
        $sheet->setCellValue('D10', $s($form->rank));
        $sheet->setCellValue('D12', $s($form->crew_name));
        $sheet->setCellValue('L10', $s($form->vessel_type));
        $sheet->setCellValue('L11', $s($form->principal_name));

        /*
           Vessel: D16
           Place: D18
           Date: L16
        */
        $sheet->setCellValue('D16', $s($form->embarkation_vessel_name));
        $sheet->setCellValue('D18', $s($form->embarkation_place));
        $sheet->setCellValue('L16', $d($form->embarkation_date));

        /*
           Date: D22
           Place: D24
           Manila Arrival: L22
        */
        $sheet->setCellValue('D22', $d($form->disembarkation_date));
        $sheet->setCellValue('D24', $s($form->disembarkation_place));
        $sheet->setCellValue('L22', $d($form->manila_arrival_date));

        /*
           Present Address: D28
           Provincial Address: D30
           Phone: D32
           Email: K32
           Date of Availability: D34
           Availability status: D36
           Next vessel assignment date: K36
           Long vacation reason: G38
        */
        $sheet->setCellValue('D28', $s($form->present_address));
        $sheet->setCellValue('D30', $s($form->provincial_address));
        $sheet->setCellValue('D32', $s($form->phone_number));
        $sheet->setCellValue('K32', $s($form->email));
        $sheet->setCellValue('D34', $d($form->date_of_availability));
        $sheet->setCellValue('D36', $s($form->availability_status));
        $sheet->setCellValue('K36', $d($form->next_vessel_assignment_date));
        $sheet->setCellValue('G38', $s($form->long_vacation_reason));

        /*
           Yes: E43, No: E45
           Types:
             Reported to Master: E48
             Lost Work Day: E50
             Needs further treatment: E52
             Other: E54
           Lost Work Days numeric: F56
           Details: F58
        */
        $hasIllness = (bool) $form->has_illness_or_injury;

        $sheet->setCellValue('E43', $hasIllness ? 'X' : '');
        $sheet->setCellValue('E45', !$hasIllness ? 'X' : '');

        $types = is_array($form->illness_injury_types) ? $form->illness_injury_types : [];

        $sheet->setCellValue('E48', in_array('reported_to_master', $types, true) ? 'X' : '');
        $sheet->setCellValue('E50', in_array('lost_work_day', $types, true) ? 'X' : '');
        $sheet->setCellValue('E52', in_array('needs_further_treatment', $types, true) ? 'X' : '');
        $sheet->setCellValue('E54', in_array('other', $types, true) ? 'X' : '');

        $sheet->setCellValue('F56', $form->lost_work_days !== null ? (int) $form->lost_work_days : '');
        $sheet->setCellValue('F58', $s($form->medical_incident_details));

        /*
           Q1: A64
           Q2: A69
           Q3: A74
           Q4: A78
           Q5: A82
           Q6: A87
        */
        $sheet->setCellValue('A64', $s($form->comment_q1_technical));
        $sheet->setCellValue('A69', $s($form->comment_q2_crewing));
        $sheet->setCellValue('A74', $s($form->comment_q3_complaint));
        $sheet->setCellValue('A78', $s($form->comment_q4_immigrant_visa));
        $sheet->setCellValue('A82', $s($form->comment_q5_commitments));
        $sheet->setCellValue('A87', $s($form->comment_q6_additional));

        // Wrap text for large cells (safe; won’t break layout)
        foreach (['D28', 'D30', 'G38', 'F58', 'A64', 'A69', 'A74', 'A78', 'A82', 'A87'] as $cell) {
            $sheet->getStyle($cell)->getAlignment()->setWrapText(true);
        }
    }
}
