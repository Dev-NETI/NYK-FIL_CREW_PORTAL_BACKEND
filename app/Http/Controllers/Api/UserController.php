<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\FormatsUserData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    use FormatsUserData;
    public function getProfile($crewId, Request $request)
    {
        try {
            $currentUser = $request->user();

            // Find the user by crew_id in user_profiles table
            $user = User::whereHas('profile', function ($query) use ($crewId) {
                $query->where('crew_id', $crewId);
            })->with(['profile', 'contacts', 'employment', 'education', 'physicalTraits'])->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Check permissions: users can only view their own profile or admin can view any profile
            $currentUserCrewId = $currentUser->profile?->crew_id;
            if ($currentUserCrewId !== $crewId && $currentUser->is_crew !== 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to view this profile'
                ], 403);
            }

            Log::info('Profile accessed', [
                'viewer_id' => $currentUser->id,
                'viewed_crew_id' => $crewId,
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'user' => $this->formatUserData($user),
                'message' => 'Profile retrieved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving user profile', [
                'crew_id' => $crewId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving the profile'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'crew_id' => 'required',
                'firstname' => 'required',
                'middlename' => 'nullable',
                'lastname' => 'required|string',
                'suffix' => 'nullable|string',
                'age' => 'nullable|integer',
                'gender' => 'required',
                'civil_status' => 'required',
                'birthdate' => 'required|date',
                'place_of_birth' => 'required|string|max:255',
                'mobile_number' => 'required|string|max:20',
                'email' => 'required|email',
                'height' => 'nullable|numeric|min:0|max:300',
                'weight' => 'nullable|numeric|min:0|max:500',
                'educational_attainments' => 'nullable|array',
                'educational_attainments.*.education_level' => 'required',
                'educational_attainments.*.degree' => 'required|string|max:255',
                'educational_attainments.*.date_graduated' => 'required|string|max:4',
                'employment_documents' => 'nullable|array',
                'employment_documents.*.employment_document_type_id' => 'required',
                'employment_documents.*.document_number' => 'nullable|string',
                'employment_documents.*.file_path' => 'nullable|string',
                'travel_documents' => 'nullable|array',
                'travel_documents.*.travel_document_type_id' => 'required',
                'travel_documents.*.id_no' => 'nullable|string',
                'travel_documents.*.file_path' => 'nullable|string',
            ]);

            DB::beginTransaction();

            try {
                // Create the user
                $user = User::create([
                    'email' => $validatedData['email'],
                    'is_crew' => true,
                    'modified_by' => 'RECRUITMENT API',
                ]);

                // Create user profile
                $profile = $user->profile()->create([
                    'crew_id' => $validatedData['crew_id'],
                    'first_name' => $validatedData['firstname'],
                    'middle_name' => $validatedData['middlename'] ?? null,
                    'last_name' => $validatedData['lastname'],
                    'suffix' => $validatedData['suffix'] ?? null,
                    'birth_date' => $validatedData['birthdate'],
                    'birth_place' => $validatedData['place_of_birth'],
                    'age' => $validatedData['age'],
                    'gender' => $validatedData['gender'],
                    'civil_status' => $validatedData['civil_status'],
                    'modified_by' => 'RECRUITMENT API',
                ]);

                // Create user contact information
                $user->contacts()->create([
                    'mobile_number' => $validatedData['mobile_number'],
                    'email_personal' => $validatedData['email'],
                    'modified_by' => 'RECRUITMENT API',
                ]);

                // Create user physical traits
                if (isset($validatedData['height']) || isset($validatedData['weight'])) {
                    $user->physicalTraits()->create([
                        'height' => $validatedData['height'] ?? null,
                        'weight' => $validatedData['weight'] ?? null,
                        'modified_by' => 'RECRUITMENT API',
                    ]);
                }

                // Create educational attainments
                if (isset($validatedData['educational_attainments'])) {
                    foreach ($validatedData['educational_attainments'] as $education) {
                        $user->educations()->create([
                            'education_level' => $education['education_level'],
                            'degree' => $education['degree'],
                            'date_graduated' => $education['date_graduated'],
                            'modified_by' => 'RECRUITMENT API',
                        ]);
                    }
                }

                // Retrieve the crew_id from the created profile
                $createdCrewId = $profile->crew_id;

                // Create employment documents using retrieved crew_id
                if (isset($validatedData['employment_documents'])) {
                    foreach ($validatedData['employment_documents'] as $document) {
                        $profile->employmentDocuments()->create([
                            'crew_id' => $createdCrewId,
                            'employment_document_type_id' => $document['employment_document_type_id'],
                            'document_number' => $document['document_number'] ?? null,
                            'file_path' => $document['file_path'] ?? null,
                            'modified_by' => 'RECRUITMENT API',
                        ]);
                    }
                }

                // Create travel documents using retrieved crew_id
                if (isset($validatedData['travel_documents'])) {
                    foreach ($validatedData['travel_documents'] as $document) {
                        $profile->travelDocuments()->create([
                            'crew_id' => $createdCrewId,
                            'travel_document_type_id' => $document['travel_document_type_id'],
                            'id_no' => $document['id_no'] ?? null,
                            'file_path' => $document['file_path'] ?? null,
                            'modified_by' => 'RECRUITMENT API',
                        ]);
                    }
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Crew member created successfully'
                ], 201);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $currentUser = $request->user();

            // Log the current user for debugging
            Log::info('Crew list access attempt', [
                'user_id' => $currentUser->id,
                'is_crew' => $currentUser->is_crew,
                'email' => $currentUser->email
            ]);

            // Get all crew members (is_crew = 1) with their related data
            $crew = User::where('is_crew', 1)
                ->with(['profile', 'contacts', 'employment.fleet', 'employment.rank', 'education', 'physicalTraits'])
                ->orderBy('created_at', 'desc')
                ->get();

            $formattedCrew = $crew->map(function ($user) {
                return $this->formatUserData($user);
            });

            Log::info('Crew list accessed', [
                'admin_id' => $currentUser->id,
                'total_crew' => $crew->count(),
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'crew' => $formattedCrew,
                'total' => $crew->count(),
                'message' => 'Crew list retrieved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving crew list', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving the crew list'
            ], 500);
        }
    }

    public function show($id, Request $request)
    {
        try {
            $currentUser = $request->user();

            // Check if user is admin
            if ($currentUser->is_crew !== 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to access this resource'
                ], 403);
            }

            $user = User::with(['profile', 'contacts', 'employment.fleet', 'employment.rank', 'education', 'physicalTraits'])->find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'user' => $this->formatUserData($user),
                'message' => 'User retrieved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving user', [
                'user_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving the user'
            ], 500);
        }
    }

    public function getProfileAdmin($id, Request $request)
    {
        try {
            $currentUser = $request->user();

            // Check if user is admin
            if ($currentUser->is_crew === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to access this resource'
                ], 403);
            }

            // Find crew by id with related data
            $crew = User::where('id', $id)
                ->where('is_crew', 1)
                ->with(['profile', 'contacts', 'employment.fleet', 'employment.rank', 'education', 'physicalTraits'])
                ->first();

            if (!$crew) {
                return response()->json([
                    'success' => false,
                    'message' => 'Crew member not found'
                ], 404);
            }

            Log::info('Crew profile accessed by admin', [
                'admin_id' => $currentUser->id,
                'crew_id' => $id,
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'user' => $this->formatUserData($crew),
                'message' => 'Crew profile retrieved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving crew profile for admin', [
                'crew_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving the crew profile'
            ], 500);
        }
    }

    public function update($id, Request $request)
    {
        try {
            $currentUser = $request->user();

            // Find the crew member
            $crew = User::where('id', $id)
                ->where('is_crew', 1)
                ->with(['profile', 'contacts', 'employment.fleet', 'employment.rank', 'education', 'physicalTraits'])
                ->first();

            if (!$crew) {
                return response()->json([
                    'success' => false,
                    'message' => 'Crew member not found'
                ], 404);
            }

            // Validate the request data
            $validatedData = $request->validate([
                'email' => [
                    'sometimes',
                    'required',
                    'email',
                    Rule::unique('users')->ignore($crew->id)
                ],

                // Profile data
                'profile.full_name' => 'sometimes|string|max:255',
                'profile.first_name' => 'sometimes|string|max:255',
                'profile.middle_name' => 'sometimes|nullable|string|max:255',
                'profile.last_name' => 'sometimes|string|max:255',
                'profile.suffix' => 'sometimes|nullable|string|max:50',
                'profile.nickname' => 'sometimes|nullable|string|max:100',
                'profile.birth_date' => 'sometimes|nullable|date',
                'profile.birth_place' => 'sometimes|nullable|string|max:255',
                'profile.gender' => 'sometimes|nullable|in:Male,Female,Other',
                'profile.civil_status' => 'sometimes|nullable|string|max:100',
                'profile.nationality' => 'sometimes|nullable|string|max:100',
                'profile.religion' => 'sometimes|nullable|string|max:100',

                // Physical traits
                'physicalTraits.height' => 'sometimes|nullable|numeric|min:0|max:300',
                'physicalTraits.weight' => 'sometimes|nullable|numeric|min:0|max:500',
                'physicalTraits.eye_color' => 'sometimes|nullable|string|max:50',
                'physicalTraits.blood_type' => 'sometimes|nullable|string|max:50',
                'physicalTraits.hair_color' => 'sometimes|nullable|string|max:50',

                // Contact information
                'contacts.email_personal' => 'sometimes|nullable|email|max:255',
                'contacts.mobile_number' => 'sometimes|nullable|string|max:20',
                'contacts.alternate_phone' => 'sometimes|nullable|string|max:20',
                'contacts.emergency_contact_name' => 'sometimes|nullable|string|max:255',
                'contacts.emergency_contact_phone' => 'sometimes|nullable|string|max:20',
                'contacts.emergency_contact_relationship' => 'sometimes|nullable|string|max:100',
                'contacts.permanent_address_id' => 'sometimes|nullable|integer',
                'contacts.current_address_id' => 'sometimes|nullable|integer',

                // Education
                'education.highest_education' => 'sometimes|nullable|string|max:255',
                'education.school_name' => 'sometimes|nullable|string|max:255',
                'education.course' => 'sometimes|nullable|string|max:255',
                'education.graduation_year' => 'sometimes|nullable|integer|min:1900|max:' . (date('Y') + 10),
            ]);

            DB::beginTransaction();

            try {
                // Update main user data
                if (isset($validatedData['email'])) {
                    $crew->email = $validatedData['email'];
                    $crew->save();
                }

                // Update profile data
                if (isset($validatedData['profile'])) {
                    // Normalize gender to proper capitalization
                    if (isset($validatedData['profile']['gender'])) {
                        $validatedData['profile']['gender'] = ucfirst(strtolower($validatedData['profile']['gender']));
                    }

                    if ($crew->profile) {
                        $crew->profile->update($validatedData['profile']);
                    } else {
                        $crew->profile()->create($validatedData['profile']);
                    }
                }

                // Update physical traits
                if (isset($validatedData['physicalTraits'])) {
                    if ($crew->physicalTraits) {
                        $crew->physicalTraits->update($validatedData['physicalTraits']);
                    } else {
                        $crew->physicalTraits()->create($validatedData['physicalTraits']);
                    }
                }

                // Update contact information
                if (isset($validatedData['contacts'])) {
                    if ($crew->contacts) {
                        $crew->contacts->update($validatedData['contacts']);
                    } else {
                        $crew->contacts()->create($validatedData['contacts']);
                    }
                }

                // Update education information
                if (isset($validatedData['education'])) {
                    if ($crew->education) {
                        $crew->education->update($validatedData['education']);
                    } else {
                        $crew->education()->create($validatedData['education']);
                    }
                }

                DB::commit();

                // Reload the crew with fresh data
                $updatedCrew = User::where('id', $id)
                    ->with(['profile', 'contacts', 'employment.fleet', 'employment.rank', 'education', 'physicalTraits'])
                    ->first();

                Log::info('Crew profile updated', [
                    'admin_id' => $currentUser->id,
                    'crew_id' => $id,
                    'updated_fields' => array_keys($validatedData),
                    'ip' => $request->ip()
                ]);

                return response()->json([
                    'success' => true,
                    'user' => $this->formatUserData($updatedCrew),
                    'message' => 'Crew profile updated successfully'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating crew profile', [
                'crew_id' => $id,
                'admin_id' => $currentUser->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the crew profile'
            ], 500);
        }
    }
}
