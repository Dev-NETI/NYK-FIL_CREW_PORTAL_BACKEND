<?php

namespace App\Http\Controllers\Api\Mpip;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\CertificateType;
use App\Models\CrewCertificate;
use App\Models\EmploymentDocument;
use App\Models\EmploymentDocumentType;
use App\Models\TravelDocument;
use App\Models\TravelDocumentType;
use App\Models\UserProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MpipDocumentUpdateController extends Controller
{
    /**
     * Create or update travel documents for a crew member.
     *
     * PUT /api/mpip/documents/travel/{crew_id}
     */
    public function updateTravelDocuments(Request $request, string $crew_id): JsonResponse
    {
        $profile = UserProfile::where('crew_id', $crew_id)->first();

        if (! $profile) {
            return response()->json([
                'success' => false,
                'message' => 'Crew member not found.',
                'error'   => "No crew record with crew_id: {$crew_id}",
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'travel_documents'                      => 'required|array|min:1',
            'travel_documents.*.document_type'      => 'required|string|max:255',
            'travel_documents.*.id_no'              => 'required|string|max:100',
            'travel_documents.*.place_of_issue'     => 'nullable|string|max:255',
            'travel_documents.*.date_of_issue'      => 'nullable|date',
            'travel_documents.*.expiration_date'    => 'nullable|date',
            'travel_documents.*.remaining_pages'    => 'nullable|integer',
            'travel_documents.*.is_us_visa'         => 'nullable|boolean',
            'travel_documents.*.visa_type'          => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 400);
        }

        $docTypes = TravelDocumentType::get(['id', 'name'])->keyBy('name')->all();

        $results = ['upserted' => [], 'skipped' => []];

        DB::transaction(function () use ($crew_id, $request, $docTypes, &$results) {
            foreach ($request->input('travel_documents') as $doc) {
                $typeId = $docTypes[$doc['document_type']]?->id ?? null;

                if (! $typeId) {
                    $results['skipped'][] = [
                        'id_no'   => $doc['id_no'],
                        'reason'  => "Unknown document type: {$doc['document_type']}",
                    ];
                    continue;
                }

                $record = TravelDocument::firstOrNew([
                    'crew_id'                 => $crew_id,
                    'travel_document_type_id' => $typeId,
                    'id_no'                   => $doc['id_no'],
                ]);

                $record->fill(array_filter([
                    'place_of_issue'  => $doc['place_of_issue']  ?? null,
                    'date_of_issue'   => $doc['date_of_issue']   ?? null,
                    'expiration_date' => $doc['expiration_date'] ?? null,
                    'remaining_pages' => $doc['remaining_pages'] ?? null,
                    'is_US_VISA'      => $doc['is_us_visa']      ?? null,
                    'visa_type'       => $doc['visa_type']       ?? null,
                ], fn($v) => $v !== null));

                $record->modified_by = 'MPIP API';
                $record->save();

                $results['upserted'][] = $doc['document_type'] . ' / ' . $doc['id_no'];
            }
        });

        return response()->json([
            'success'  => true,
            'message'  => 'Travel documents sync completed.',
            'crew_id'  => $crew_id,
            'summary'  => [
                'upserted' => count($results['upserted']),
                'skipped'  => count($results['skipped']),
            ],
            'details'    => $results,
            'timestamp'  => now()->toIso8601String(),
        ]);
    }

    /**
     * Create or update employment documents for a crew member.
     *
     * PUT /api/mpip/documents/employment/{crew_id}
     */
    public function updateEmploymentDocuments(Request $request, string $crew_id): JsonResponse
    {
        $profile = UserProfile::where('crew_id', $crew_id)->first();

        if (! $profile) {
            return response()->json([
                'success' => false,
                'message' => 'Crew member not found.',
                'error'   => "No crew record with crew_id: {$crew_id}",
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'employment_documents'                   => 'required|array|min:1',
            'employment_documents.*.document_type'   => 'required|string|max:255',
            'employment_documents.*.document_number' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 400);
        }

        $docTypes = EmploymentDocumentType::get(['id', 'name'])->keyBy('name')->all();

        $results = ['upserted' => [], 'skipped' => []];

        DB::transaction(function () use ($crew_id, $request, $docTypes, &$results) {
            foreach ($request->input('employment_documents') as $doc) {
                $typeId = $docTypes[$doc['document_type']]?->id ?? null;

                if (! $typeId) {
                    $results['skipped'][] = [
                        'document_type' => $doc['document_type'],
                        'reason'        => "Unknown document type: {$doc['document_type']}",
                    ];
                    continue;
                }

                $record = EmploymentDocument::firstOrNew([
                    'crew_id'                     => $crew_id,
                    'employment_document_type_id' => $typeId,
                    'document_number'             => $doc['document_number'] ?? null,
                ]);

                $record->modified_by = 'MPIP API';
                $record->save();

                $results['upserted'][] = $doc['document_type'] . ($doc['document_number'] ? ' / ' . $doc['document_number'] : '');
            }
        });

        return response()->json([
            'success'  => true,
            'message'  => 'Employment documents sync completed.',
            'crew_id'  => $crew_id,
            'summary'  => [
                'upserted' => count($results['upserted']),
                'skipped'  => count($results['skipped']),
            ],
            'details'   => $results,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Create or update certificates for a crew member.
     *
     * PUT /api/mpip/documents/certificates/{crew_id}
     */
    public function updateCertificates(Request $request, string $crew_id): JsonResponse
    {
        $profile = UserProfile::where('crew_id', $crew_id)->first();

        if (! $profile) {
            return response()->json([
                'success' => false,
                'message' => 'Crew member not found.',
                'error'   => "No crew record with crew_id: {$crew_id}",
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'certificates'                    => 'required|array|min:1',
            'certificates.*.certificate_type' => 'nullable|string|max:255',
            'certificates.*.certificate_name' => 'required|string|max:255',
            'certificates.*.certificate_no'   => 'nullable|string|max:100',
            'certificates.*.issued_by'        => 'nullable|string|max:255',
            'certificates.*.date_issued'      => 'nullable|date',
            'certificates.*.expiry_date'      => 'nullable|date',
            'certificates.*.grade'            => 'nullable|string|max:100',
            'certificates.*.rank_permitted'   => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 400);
        }

        $certificatesByName = Certificate::get(['id', 'name'])->keyBy('name')->all();

        $results = ['upserted' => [], 'skipped' => []];

        DB::transaction(function () use ($crew_id, $request, &$certificatesByName, &$results) {
            foreach ($request->input('certificates') as $cert) {
                $certificate = $certificatesByName[$cert['certificate_name']] ?? null;

                // Auto-create the master certificate record if name not found and type is provided
                if (! $certificate && ! empty($cert['certificate_type'])) {
                    $certType = CertificateType::where('name', $cert['certificate_type'])->first();
                    if ($certType) {
                        $certificate = Certificate::firstOrCreate(
                            ['name' => $cert['certificate_name']],
                            [
                                'certificate_type_id' => $certType->id,
                                'modified_by'         => 'MPIP API',
                            ]
                        );
                        $certificatesByName[$certificate->name] = $certificate;
                    }
                }

                if (! $certificate) {
                    $results['skipped'][] = [
                        'certificate_name' => $cert['certificate_name'],
                        'reason'           => "Certificate '{$cert['certificate_name']}' not found. Provide certificate_type to auto-create.",
                    ];
                    continue;
                }

                $record = CrewCertificate::firstOrNew([
                    'crew_id'        => $crew_id,
                    'certificate_id' => $certificate->id,
                    'certificate_no' => $cert['certificate_no'] ?? null,
                ]);

                $record->fill(array_filter([
                    'grade'          => $cert['grade']          ?? null,
                    'rank_permitted' => $cert['rank_permitted'] ?? null,
                    'issued_by'      => $cert['issued_by']      ?? null,
                    'date_issued'    => $cert['date_issued']    ?? null,
                    'expiry_date'    => $cert['expiry_date']    ?? null,
                ], fn($v) => $v !== null));

                $record->modified_by = 'MPIP API';
                $record->save();

                $results['upserted'][] = $cert['certificate_name'];
            }
        });

        return response()->json([
            'success'  => true,
            'message'  => 'Certificates sync completed.',
            'crew_id'  => $crew_id,
            'summary'  => [
                'upserted' => count($results['upserted']),
                'skipped'  => count($results['skipped']),
            ],
            'details'   => $results,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
