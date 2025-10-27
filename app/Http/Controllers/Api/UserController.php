<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\FormatsUserData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
                'educational_attainments.*.date_graduated' => 'required',
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
                'email' => $currentUser->email,
                'params' => $request->all()
            ]);

            // Validate pagination and search parameters
            $validatedData = $request->validate([
                'page' => 'sometimes|integer|min:1|max:1000',
                'per_page' => 'sometimes|integer|min:1|max:100',
                'search' => 'sometimes|nullable|string|max:255',
                'status' => 'sometimes|string|in:all,active,inactive,on_leave',
                'sort_by' => 'sometimes|string|in:first_name,email',
                'sort_order' => 'sometimes|string|in:asc,desc'
            ]);

            // Set defaults
            $perPage = $validatedData['per_page'] ?? 10;
            $search = $validatedData['search'] ?? '';
            $status = $validatedData['status'] ?? 'all';
            $sortBy = $validatedData['sort_by'] ?? 'first_name';
            $sortOrder = $validatedData['sort_order'] ?? 'asc';

            // Build query for crew members (is_crew = 1) with their related data
            $query = User::where('is_crew', 1)
                ->with(['profile', 'contacts', 'employment', 'education', 'physicalTraits']);

            // Apply search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('email', 'like', "%{$search}%")
                        ->orWhereHas('profile', function ($profileQuery) use ($search) {
                            $profileQuery->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                });
            }

            // Apply status filter
            if ($status !== 'all') {
                $query->whereHas('employment', function ($employmentQuery) use ($status) {
                    $employmentQuery->where('crew_status', $status);
                });
            }

            // Apply sorting with proper joins when needed
            switch ($sortBy) {
                case 'first_name':
                    $query->leftJoin('user_profiles as up', 'users.id', '=', 'up.user_id')
                        ->orderByRaw("COALESCE(CONCAT(up.first_name, ' ', up.last_name), users.email) {$sortOrder}")
                        ->select('users.*');
                    break;
                case 'email':
                default:
                    $query->orderBy('users.email', $sortOrder);
                    break;
            }

            // Get paginated results
            $paginatedCrew = $query->paginate($perPage);

            // Format the crew data
            $formattedCrew = $paginatedCrew->getCollection()->map(function ($user) {
                return $this->formatUserData($user);
            });

            // Update the collection with formatted data
            $paginatedCrew->setCollection($formattedCrew);

            Log::info('Crew list accessed successfully', [
                'admin_id' => $currentUser->id,
                'total_crew' => $paginatedCrew->total(),
                'page' => $paginatedCrew->currentPage(),
                'per_page' => $paginatedCrew->perPage(),
                'search' => $search,
                'status' => $status,
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'crew' => $formattedCrew,
                'pagination' => [
                    'current_page' => $paginatedCrew->currentPage(),
                    'per_page' => $paginatedCrew->perPage(),
                    'total' => $paginatedCrew->total(),
                    'last_page' => $paginatedCrew->lastPage(),
                    'from' => $paginatedCrew->firstItem(),
                    'to' => $paginatedCrew->lastItem(),
                    'has_more_pages' => $paginatedCrew->hasMorePages(),
                ],
                'filters' => [
                    'search' => $search,
                    'status' => $status,
                    'sort_by' => $sortBy,
                    'sort_order' => $sortOrder,
                ],
                'message' => 'Crew list retrieved successfully'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error in crew list', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid parameters provided',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error retrieving crew list', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->all(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving the crew list',
                'debug' => config('app.debug') ? [
                    'message' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => basename($e->getFile())
                ] : null
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
            ], [
                // Custom validation messages
                'email.required' => 'Email address is required.',
                'email.email' => 'Please enter a valid email address.',
                'email.unique' => 'This email address is already in use.',

                // Profile validation messages
                'profile.first_name.required' => 'First name is required.',
                'profile.first_name.string' => 'First name must be text.',
                'profile.first_name.max' => 'First name cannot exceed 255 characters.',
                'profile.last_name.required' => 'Last name is required.',
                'profile.last_name.string' => 'Last name must be text.',
                'profile.last_name.max' => 'Last name cannot exceed 255 characters.',
                'profile.middle_name.string' => 'Middle name must be text.',
                'profile.middle_name.max' => 'Middle name cannot exceed 255 characters.',
                'profile.suffix.string' => 'Suffix must be text.',
                'profile.suffix.max' => 'Suffix cannot exceed 50 characters.',
                'profile.birth_date.date' => 'Please enter a valid birth date.',
                'profile.birth_place.string' => 'Birth place must be text.',
                'profile.birth_place.max' => 'Birth place cannot exceed 255 characters.',
                'profile.gender.in' => 'Please select a valid gender option.',
                'profile.civil_status.string' => 'Civil status must be text.',
                'profile.civil_status.max' => 'Civil status cannot exceed 100 characters.',
                'profile.nationality.string' => 'Nationality must be text.',
                'profile.nationality.max' => 'Nationality cannot exceed 100 characters.',
                'profile.religion.string' => 'Religion must be text.',
                'profile.religion.max' => 'Religion cannot exceed 100 characters.',

                // Physical traits validation messages
                'physicalTraits.height.numeric' => 'Height must be a number.',
                'physicalTraits.height.min' => 'Height cannot be negative.',
                'physicalTraits.height.max' => 'Height cannot exceed 300 cm.',
                'physicalTraits.weight.numeric' => 'Weight must be a number.',
                'physicalTraits.weight.min' => 'Weight cannot be negative.',
                'physicalTraits.weight.max' => 'Weight cannot exceed 500 kg.',
                'physicalTraits.blood_type.string' => 'Blood type must be text.',
                'physicalTraits.blood_type.max' => 'Blood type cannot exceed 50 characters.',
                'physicalTraits.eye_color.string' => 'Eye color must be text.',
                'physicalTraits.eye_color.max' => 'Eye color cannot exceed 50 characters.',
                'physicalTraits.hair_color.string' => 'Hair color must be text.',
                'physicalTraits.hair_color.max' => 'Hair color cannot exceed 50 characters.',

                // Contact validation messages
                'contacts.email_personal.email' => 'Please enter a valid personal email address.',
                'contacts.email_personal.max' => 'Personal email cannot exceed 255 characters.',
                'contacts.mobile_number.string' => 'Mobile number must be text.',
                'contacts.mobile_number.max' => 'Mobile number cannot exceed 20 characters.',
                'contacts.alternate_phone.string' => 'Alternate phone must be text.',
                'contacts.alternate_phone.max' => 'Alternate phone cannot exceed 20 characters.',
                'contacts.emergency_contact_name.string' => 'Emergency contact name must be text.',
                'contacts.emergency_contact_name.max' => 'Emergency contact name cannot exceed 255 characters.',
                'contacts.emergency_contact_phone.string' => 'Emergency contact phone must be text.',
                'contacts.emergency_contact_phone.max' => 'Emergency contact phone cannot exceed 20 characters.',
                'contacts.emergency_contact_relationship.string' => 'Emergency contact relationship must be text.',
                'contacts.emergency_contact_relationship.max' => 'Emergency contact relationship cannot exceed 100 characters.',
                'contacts.permanent_address_id.integer' => 'Permanent address ID must be a number.',
                'contacts.current_address_id.integer' => 'Current address ID must be a number.',
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

    public function storeEducationInformation(Request $request, $id)
    {
        try {
            $currentUser = Auth::user();

            // Find the crew member
            $crew = User::where('id', $id)
                ->where('is_crew', 1)
                ->with(['educations'])
                ->first();

            if (!$crew) {
                return response()->json([
                    'success' => false,
                    'message' => 'Crew member not found'
                ], 404);
            }

            // Validate education data for high school, college, and higher education
            $validatedData = $request->validate([
                'high_school.school_name' => 'sometimes|required|string|max:255',
                'high_school.date_graduated' => 'sometimes|nullable|date',
                'high_school.degree' => 'sometimes|nullable|string|max:255',

                'college.school_name' => 'sometimes|required|string|max:255',
                'college.date_graduated' => 'sometimes|nullable|date',
                'college.degree' => 'sometimes|nullable|string|max:255',

                'higher_education.school_name' => 'sometimes|required|string|max:255',
                'higher_education.date_graduated' => 'sometimes|nullable|date',
                'higher_education.degree' => 'sometimes|nullable|string|max:255',
            ]);

            DB::beginTransaction();

            try {
                $educationRecords = [];

                // Create high school education record
                if (isset($validatedData['high_school'])) {
                    $highSchool = $crew->educations()->create([
                        'school_name' => $validatedData['high_school']['school_name'],
                        'date_graduated' => $validatedData['high_school']['date_graduated'] ?? null,
                        'degree' => $validatedData['high_school']['degree'] ?? null,
                        'education_level' => 'high_school',
                        'modified_by' => $currentUser->id ?? 'System',
                    ]);
                    $educationRecords['high_school'] = $highSchool;
                }

                // Create college education record
                if (isset($validatedData['college'])) {
                    $college = $crew->educations()->create([
                        'school_name' => $validatedData['college']['school_name'],
                        'date_graduated' => $validatedData['college']['date_graduated'] ?? null,
                        'degree' => $validatedData['college']['degree'] ?? null,
                        'education_level' => 'college',
                        'modified_by' => $currentUser->id ?? 'System',
                    ]);
                    $educationRecords['college'] = $college;
                }

                // Create higher education record
                if (isset($validatedData['higher_education'])) {
                    $higherEducation = $crew->educations()->create([
                        'school_name' => $validatedData['higher_education']['school_name'],
                        'date_graduated' => $validatedData['higher_education']['date_graduated'] ?? null,
                        'degree' => $validatedData['higher_education']['degree'] ?? null,
                        'education_level' => 'higher_educational',
                        'modified_by' => $currentUser->id ?? 'System',
                    ]);
                    $educationRecords['higher_education'] = $higherEducation;
                }

                DB::commit();

                Log::info('Education information created', [
                    'admin_id' => $currentUser->id,
                    'crew_id' => $id,
                    'education_levels' => array_keys($educationRecords),
                    'ip' => $request->ip()
                ]);

                return response()->json([
                    'success' => true,
                    'data' => $educationRecords,
                    'message' => 'Education information created successfully'
                ], 201);
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
            Log::error('Error creating education information', [
                'crew_id' => $id,
                'admin_id' => $currentUser->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating education information'
            ], 500);
        }
    }

    public function updateEducationInformation(Request $request, $id)
    {
        try {
            $currentUser = Auth::user();

            // Find the crew member
            $crew = User::where('id', $id)
                ->where('is_crew', 1)
                ->with(['educations'])
                ->first();

            if (!$crew) {
                return response()->json([
                    'success' => false,
                    'message' => 'Crew member not found'
                ], 404);
            }

            // Validate education data for high school, college, and higher education
            $validatedData = $request->validate([
                'high_school.school_name' => 'sometimes|required|string|max:255',
                'high_school.date_graduated' => 'sometimes|nullable|date',
                'high_school.degree' => 'sometimes|nullable|string|max:255',

                'college.school_name' => 'sometimes|required|string|max:255',
                'college.date_graduated' => 'sometimes|nullable|date',
                'college.degree' => 'sometimes|nullable|string|max:255',

                'higher_education.school_name' => 'sometimes|required|string|max:255',
                'higher_education.date_graduated' => 'sometimes|nullable|date',
                'higher_education.degree' => 'sometimes|nullable|string|max:255',
            ]);

            DB::beginTransaction();

            try {
                $updatedRecords = [];

                // Handle high school education update (creates new record if doesn't exist)
                if (isset($validatedData['high_school'])) {
                    $existingHighSchool = $crew->educations()->where('education_level', 'high_school')->first();

                    if ($existingHighSchool) {
                        $existingHighSchool->update([
                            'school_name' => $validatedData['high_school']['school_name'],
                            'date_graduated' => $validatedData['high_school']['date_graduated'] ?? null,
                            'degree' => $validatedData['high_school']['degree'] ?? null,
                            'modified_by' => 2,
                        ]);
                        $updatedRecords['high_school'] = $existingHighSchool->fresh();
                    } else {
                        // Create new record if doesn't exist
                        $highSchool = $crew->educations()->create([
                            'school_name' => $validatedData['high_school']['school_name'],
                            'date_graduated' => $validatedData['high_school']['date_graduated'] ?? null,
                            'degree' => $validatedData['high_school']['degree'] ?? null,
                            'education_level' => 'high_school',
                            'modified_by' => 2,
                        ]);
                        $updatedRecords['high_school'] = $highSchool;
                    }
                }

                // Handle college education update (creates new record if doesn't exist)
                if (isset($validatedData['college'])) {
                    $existingCollege = $crew->educations()->where('education_level', 'college')->first();

                    if ($existingCollege) {
                        $existingCollege->update([
                            'school_name' => $validatedData['college']['school_name'],
                            'date_graduated' => $validatedData['college']['date_graduated'] ?? null,
                            'degree' => $validatedData['college']['degree'] ?? null,
                            'modified_by' => 2,
                        ]);
                        $updatedRecords['college'] = $existingCollege->fresh();
                    } else {
                        // Create new record if doesn't exist
                        $college = $crew->educations()->create([
                            'school_name' => $validatedData['college']['school_name'],
                            'date_graduated' => $validatedData['college']['date_graduated'] ?? null,
                            'degree' => $validatedData['college']['degree'] ?? null,
                            'education_level' => 'college',
                            'modified_by' => 2,
                        ]);
                        $updatedRecords['college'] = $college;
                    }
                }

                // Handle higher education update (creates new record if doesn't exist)
                if (isset($validatedData['higher_education'])) {
                    $existingHigherEducation = $crew->educations()->where('education_level', 'higher_educational')->first();

                    if ($existingHigherEducation) {
                        $existingHigherEducation->update([
                            'school_name' => $validatedData['higher_education']['school_name'],
                            'date_graduated' => $validatedData['higher_education']['date_graduated'] ?? null,
                            'degree' => $validatedData['higher_education']['degree'] ?? null,
                            'modified_by' => 2,
                        ]);
                        $updatedRecords['higher_education'] = $existingHigherEducation->fresh();
                    } else {
                        // Create new record if doesn't exist
                        $higherEducation = $crew->educations()->create([
                            'school_name' => $validatedData['higher_education']['school_name'],
                            'date_graduated' => $validatedData['higher_education']['date_graduated'] ?? null,
                            'degree' => $validatedData['higher_education']['degree'] ?? null,
                            'education_level' => 'higher_educational',
                            'modified_by' => 2,
                        ]);
                        $updatedRecords['higher_education'] = $higherEducation;
                    }
                }

                DB::commit();

                Log::info('Education information updated', [
                    'admin_id' => $currentUser->id,
                    'crew_id' => $id,
                    'education_levels' => array_keys($updatedRecords),
                    'ip' => $request->ip()
                ]);

                return response()->json([
                    'success' => true,
                    'data' => $updatedRecords,
                    'message' => 'Education information updated successfully'
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
            Log::error('Error updating education information', [
                'crew_id' => $id,
                'admin_id' => $currentUser->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating education information'
            ], 500);
        }
    }
}
