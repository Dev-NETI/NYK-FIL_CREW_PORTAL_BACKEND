<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProfileUpdateRequest;
use App\Models\User;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ProfileUpdateRequestController extends Controller
{
    /**
     * Store a newly created profile update request.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'crew_id' => 'required|exists:users,id',
                'section' => 'required|in:basic,contact,physical,education',
                'requested_data' => 'required|array',
            ]);

            // Get the crew member's current profile data
            $crew = User::with(['profile', 'contacts.permanentAddress', 'contacts.currentAddress', 'physical_traits', 'education'])->findOrFail($validated['crew_id']);
            
            // Extract current data for the section
            $currentData = $this->getCurrentSectionData($crew, $validated['section']);
            
            // Check if there's already a pending request for this crew and section
            $existingRequest = ProfileUpdateRequest::where('crew_id', $validated['crew_id'])
                ->where('section', $validated['section'])
                ->where('status', 'pending')
                ->first();

            if ($existingRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'There is already a pending update request for this section. Please wait for admin approval.',
                ], 422);
            }

            $updateRequest = ProfileUpdateRequest::create([
                'crew_id' => $validated['crew_id'],
                'section' => $validated['section'],
                'current_data' => $currentData,
                'requested_data' => $validated['requested_data'],
                'status' => 'pending',
            ]);

            $updateRequest->load(['crew', 'reviewer']);

            return response()->json([
                'success' => true,
                'message' => 'Profile update request submitted successfully. Waiting for admin approval.',
                'data' => $updateRequest,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit profile update request',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get profile update requests for a crew member.
     */
    public function getCrewRequests($crewId): JsonResponse
    {
        try {
            $requests = ProfileUpdateRequest::where('crew_id', $crewId)
                ->with(['crew', 'reviewer'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $requests,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch profile update requests',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get pending profile update requests for admin.
     */
    public function getPendingRequests(): JsonResponse
    {
        try {
            $requests = ProfileUpdateRequest::pending()
                ->with(['crew.profile', 'reviewer'])
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $requests,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch pending requests',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Approve a profile update request.
     */
    public function approve($id): JsonResponse
    {
        try {
            $updateRequest = ProfileUpdateRequest::findOrFail($id);
            
            if (!$updateRequest->isPending()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Request has already been processed',
                ], 422);
            }

            // Apply the changes to the crew's profile
            $this->applyProfileChanges($updateRequest);

            // Mark request as approved
            $updateRequest->approve(auth()->id());

            $updateRequest->load(['crew', 'reviewer']);

            return response()->json([
                'success' => true,
                'message' => 'Profile update request approved and changes applied successfully',
                'data' => $updateRequest,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve profile update request',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reject a profile update request.
     */
    public function reject(Request $request, $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'rejection_reason' => 'required|string|max:500',
            ]);

            $updateRequest = ProfileUpdateRequest::findOrFail($id);
            
            if (!$updateRequest->isPending()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Request has already been processed',
                ], 422);
            }

            $updateRequest->reject(auth()->id(), $validated['rejection_reason']);

            $updateRequest->load(['crew', 'reviewer']);

            return response()->json([
                'success' => true,
                'message' => 'Profile update request rejected',
                'data' => $updateRequest,
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject profile update request',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get current profile data for a section.
     */
    private function getCurrentSectionData(User $crew, string $section): array
    {
        switch ($section) {
            case 'basic':
                return [
                    'profile' => $crew->profile?->toArray(),
                    'physical_traits' => $crew->physical_traits?->toArray(),
                ];
            case 'contact':
                return [
                    'contacts' => $crew->contacts?->toArray(),
                    'permanent_address' => $crew->contacts?->permanentAddress?->toArray(),
                    'current_address' => $crew->contacts?->currentAddress?->toArray(),
                    // Include individual address fields for compatibility
                    'permanent_region' => $crew->contacts?->permanentAddress?->region_id,
                    'permanent_province' => $crew->contacts?->permanentAddress?->province_id,
                    'permanent_city' => $crew->contacts?->permanentAddress?->city_id,
                    'permanent_barangay' => $crew->contacts?->permanentAddress?->brgy_id,
                    'permanent_street' => $crew->contacts?->permanentAddress?->street_address,
                    'permanent_postal_code' => $crew->contacts?->permanentAddress?->zip_code,
                    'current_region' => $crew->contacts?->currentAddress?->region_id,
                    'current_province' => $crew->contacts?->currentAddress?->province_id,
                    'current_city' => $crew->contacts?->currentAddress?->city_id,
                    'current_barangay' => $crew->contacts?->currentAddress?->brgy_id,
                    'current_street' => $crew->contacts?->currentAddress?->street_address,
                    'current_postal_code' => $crew->contacts?->currentAddress?->zip_code,
                ];
            case 'physical':
                return [
                    'physical_traits' => $crew->physical_traits?->toArray(),
                ];
            case 'education':
                return [
                    'education' => $crew->education?->toArray(),
                ];
            default:
                return [];
        }
    }

    /**
     * Apply approved profile changes to the crew member.
     */
    private function applyProfileChanges(ProfileUpdateRequest $updateRequest): void
    {
        $crew = $updateRequest->crew;
        $requestedData = $updateRequest->requested_data;

        switch ($updateRequest->section) {
            case 'basic':
                if (isset($requestedData['profile'])) {
                    $crew->profile()->updateOrCreate(
                        ['user_id' => $crew->id],
                        $requestedData['profile']
                    );
                }
                if (isset($requestedData['physical_traits'])) {
                    $crew->physical_traits()->updateOrCreate(
                        ['user_id' => $crew->id],
                        $requestedData['physical_traits']
                    );
                }
                break;

            case 'contact':
                if (isset($requestedData['contacts'])) {
                    $crew->contacts()->updateOrCreate(
                        ['user_id' => $crew->id],
                        $requestedData['contacts']
                    );
                }
                
                // Handle individual address fields from frontend
                $permanentAddressData = [];
                $currentAddressData = [];
                
                $permanentFields = [
                    'permanent_region' => 'region_id',
                    'permanent_province' => 'province_id', 
                    'permanent_city' => 'city_id',
                    'permanent_barangay' => 'brgy_id',
                    'permanent_street' => 'street_address',
                    'permanent_postal_code' => 'zip_code',
                ];
                
                $currentFields = [
                    'current_region' => 'region_id',
                    'current_province' => 'province_id',
                    'current_city' => 'city_id', 
                    'current_barangay' => 'brgy_id',
                    'current_street' => 'street_address',
                    'current_postal_code' => 'zip_code',
                ];
                
                foreach ($permanentFields as $frontendField => $dbField) {
                    if (isset($requestedData[$frontendField])) {
                        $permanentAddressData[$dbField] = $requestedData[$frontendField];
                    }
                }
                
                foreach ($currentFields as $frontendField => $dbField) {
                    if (isset($requestedData[$frontendField])) {
                        $currentAddressData[$dbField] = $requestedData[$frontendField];
                    }
                }
                
                // Update permanent address
                if (!empty($permanentAddressData) && $crew->contacts) {
                    if ($crew->contacts->permanent_address_id) {
                        $crew->contacts->permanentAddress->update($permanentAddressData);
                    } else {
                        $permanentAddress = Address::create(array_merge(
                            $permanentAddressData,
                            ['type' => 'permanent', 'user_id' => $crew->id]
                        ));
                        $crew->contacts->update(['permanent_address_id' => $permanentAddress->id]);
                    }
                }
                
                // Update current address
                if (!empty($currentAddressData) && $crew->contacts) {
                    if ($crew->contacts->current_address_id) {
                        $crew->contacts->currentAddress->update($currentAddressData);
                    } else {
                        $currentAddress = Address::create(array_merge(
                            $currentAddressData,
                            ['type' => 'current', 'user_id' => $crew->id]
                        ));
                        $crew->contacts->update(['current_address_id' => $currentAddress->id]);
                    }
                }
                break;

            case 'physical':
                if (isset($requestedData['physical_traits'])) {
                    $crew->physical_traits()->updateOrCreate(
                        ['user_id' => $crew->id],
                        $requestedData['physical_traits']
                    );
                }
                break;

            case 'education':
                if (isset($requestedData['education'])) {
                    $crew->education()->updateOrCreate(
                        ['user_id' => $crew->id],
                        $requestedData['education']
                    );
                }
                break;
        }
    }
}
