<?php

namespace App\Http\Controllers\Api\Mpip;

use App\Http\Controllers\Controller;
use App\Models\Rank;
use App\Models\VesselType;
use App\Models\WageScale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MpipWageSyncController extends Controller
{
    /**
     * Sync wage scale table received from MPIP.
     *
     * POST /api/mpip/wages/sync
     */
    public function sync(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'wages'                                  => 'required|array|min:1',
            'wages.*.rank_code'                      => 'nullable|string|max:50',
            'wages.*.vessel_type_name'               => 'nullable|string|max:255',
            'wages.*.effective_date'                 => 'required|date',
            'wages.*.basic_wage'                     => 'required|numeric|min:0',
            'wages.*.fixed_overtime'                 => 'nullable|numeric|min:0',
            'wages.*.leave_pay'                      => 'nullable|numeric|min:0',
            'wages.*.subsistence_allowance'          => 'nullable|numeric|min:0',
            'wages.*.vacation_leave_conversion'      => 'nullable|numeric|min:0',
            'wages.*.total_guaranteed_monthly'       => 'required|numeric|min:0',
            'wages.*.currency'                       => 'nullable|string|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $results = [
            'upserted' => 0,
            'errors'   => [],
        ];

        DB::transaction(function () use ($request, &$results) {
            $ranksByCode        = Rank::whereNotNull('code')->get(['id', 'code'])->keyBy('code');
            $vesselTypesByName  = VesselType::get(['id', 'name'])->keyBy('name');

            foreach ($request->wages as $index => $wageData) {
                try {
                    $rankId       = null;
                    $vesselTypeId = null;

                    if (! empty($wageData['rank_code'])) {
                        $rankId = $ranksByCode->get($wageData['rank_code'])?->id;
                    }

                    if (! empty($wageData['vessel_type_name'])) {
                        $vesselTypeId = $vesselTypesByName->get($wageData['vessel_type_name'])?->id;
                    }

                    $payload = [
                        'basic_wage'                => $wageData['basic_wage'],
                        'fixed_overtime'            => $wageData['fixed_overtime']           ?? 0,
                        'leave_pay'                 => $wageData['leave_pay']                ?? 0,
                        'subsistence_allowance'     => $wageData['subsistence_allowance']    ?? 0,
                        'vacation_leave_conversion' => $wageData['vacation_leave_conversion'] ?? 0,
                        'total_guaranteed_monthly'  => $wageData['total_guaranteed_monthly'],
                        'currency'                  => $wageData['currency']                 ?? 'USD',
                        'modified_by'               => 'MPIP API',
                    ];

                    WageScale::updateOrCreate(
                        [
                            'rank_id'        => $rankId,
                            'vessel_type_id' => $vesselTypeId,
                            'effective_date' => $wageData['effective_date'],
                        ],
                        $payload
                    );

                    $results['upserted']++;
                } catch (\Throwable $e) {
                    $results['errors'][] = [
                        'index'   => $index,
                        'message' => $e->getMessage(),
                    ];
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Wage scale sync completed.',
            'summary' => [
                'upserted' => $results['upserted'],
                'errors'   => count($results['errors']),
            ],
            'details' => ['errors' => $results['errors']],
        ]);
    }
}
