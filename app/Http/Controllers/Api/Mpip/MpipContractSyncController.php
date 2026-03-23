<?php

namespace App\Http\Controllers\Api\Mpip;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Rank;
use App\Models\User;
use App\Models\Vessel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MpipContractSyncController extends Controller
{
    /**
     * Sync contracts received from MPIP.
     *
     * POST /api/mpip/contracts/sync
     */
    public function sync(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'contracts'                              => 'required|array|min:1',
            'contracts.*.contract_number'            => 'required|string|max:100',
            'contracts.*.email'                      => 'required|email',
            'contracts.*.vessel_name'                => 'nullable|string|max:255',
            'contracts.*.rank_code'                  => 'nullable|string|max:50',
            'contracts.*.port_of_departure'          => 'nullable|string|max:255',
            'contracts.*.port_of_arrival'            => 'nullable|string|max:255',
            'contracts.*.contract_start_date'        => 'required|date',
            'contracts.*.contract_end_date'          => 'nullable|date',
            'contracts.*.duration_months'            => 'nullable|integer|min:1',
            'contracts.*.departure_date'             => 'nullable|date',
            'contracts.*.arrival_date'               => 'nullable|date',
            'contracts.*.basic_wage'                 => 'nullable|numeric|min:0',
            'contracts.*.fixed_overtime'             => 'nullable|numeric|min:0',
            'contracts.*.leave_pay'                  => 'nullable|numeric|min:0',
            'contracts.*.subsistence_allowance'      => 'nullable|numeric|min:0',
            'contracts.*.vacation_leave_conversion'  => 'nullable|numeric|min:0',
            'contracts.*.total_guaranteed_monthly'   => 'nullable|numeric|min:0',
            'contracts.*.currency'                   => 'nullable|string|max:10',
            'contracts.*.contract_status'            => 'nullable|string|max:50',
            'contracts.*.remarks'                    => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $results = [
            'upserted' => [],
            'skipped'  => [],
            'errors'   => [],
        ];

        DB::transaction(function () use ($request, &$results) {
            $ranksByCode   = Rank::whereNotNull('code')->get(['id', 'code'])->keyBy('code');
            $vesselsByName = Vessel::get(['id', 'name'])->keyBy('name');

            foreach ($request->contracts as $contractData) {
                try {
                    $user = User::where('email', $contractData['email'])
                        ->where('is_crew', true)
                        ->first();

                    if (! $user) {
                        $results['skipped'][] = [
                            'contract_number' => $contractData['contract_number'],
                            'reason'          => 'No crew member found with email: ' . $contractData['email'],
                        ];
                        continue;
                    }

                    $vesselId = null;
                    if (! empty($contractData['vessel_name'])) {
                        $vesselId = $vesselsByName->get($contractData['vessel_name'])?->id;
                    }

                    $rankId = null;
                    if (! empty($contractData['rank_code'])) {
                        $rankId = $ranksByCode->get($contractData['rank_code'])?->id;
                    }

                    $payload = [
                        'user_id'                    => $user->id,
                        'vessel_id'                  => $vesselId,
                        'rank_id'                    => $rankId,
                        'port_of_departure'          => $contractData['port_of_departure']         ?? null,
                        'port_of_arrival'            => $contractData['port_of_arrival']           ?? null,
                        'contract_start_date'        => $contractData['contract_start_date'],
                        'contract_end_date'          => $contractData['contract_end_date']         ?? null,
                        'duration_months'            => $contractData['duration_months']           ?? null,
                        'departure_date'             => $contractData['departure_date']            ?? null,
                        'arrival_date'               => $contractData['arrival_date']              ?? null,
                        'basic_wage'                 => $contractData['basic_wage']                ?? null,
                        'fixed_overtime'             => $contractData['fixed_overtime']            ?? null,
                        'leave_pay'                  => $contractData['leave_pay']                 ?? null,
                        'subsistence_allowance'      => $contractData['subsistence_allowance']     ?? null,
                        'vacation_leave_conversion'  => $contractData['vacation_leave_conversion'] ?? null,
                        'total_guaranteed_monthly'   => $contractData['total_guaranteed_monthly']  ?? null,
                        'currency'                   => $contractData['currency']                  ?? 'USD',
                        'contract_status'            => $contractData['contract_status']           ?? null,
                        'remarks'                    => $contractData['remarks']                   ?? null,
                        'modified_by'                => 'MPIP API',
                    ];

                    Contract::withoutTrashed()
                        ->updateOrCreate(
                            ['contract_number' => $contractData['contract_number']],
                            $payload
                        );

                    $results['upserted'][] = $contractData['contract_number'];
                } catch (\Throwable $e) {
                    $results['errors'][] = [
                        'contract_number' => $contractData['contract_number'],
                        'message'         => $e->getMessage(),
                    ];
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Contracts sync completed.',
            'summary' => [
                'upserted' => count($results['upserted']),
                'skipped'  => count($results['skipped']),
                'errors'   => count($results['errors']),
            ],
            'details' => $results,
        ]);
    }
}
