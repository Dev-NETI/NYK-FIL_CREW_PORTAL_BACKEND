<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TravelDocumentUpdateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'travel_document_id' => $this->travel_document_id,
            'crew_id' => $this->crew_id,
            'original_data' => $this->original_data,
            'updated_data' => $this->updated_data,
            'status' => $this->status,
            'reviewed_by' => $this->reviewed_by,
            'reviewed_at' => $this->reviewed_at,
            'rejection_reason' => $this->rejection_reason,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'modified_by' => $this->modified_by,

            // Explicitly include relationships with proper naming
            'travelDocument' => $this->whenLoaded('travelDocument', function () {
                $doc = $this->travelDocument;
                return [
                    'id' => $doc->id,
                    'crew_id' => $doc->crew_id,
                    'travel_document_type_id' => $doc->travel_document_type_id,
                    'id_no' => $doc->id_no,
                    'place_of_issue' => $doc->place_of_issue,
                    'date_of_issue' => $doc->date_of_issue,
                    'expiration_date' => $doc->expiration_date,
                    'remaining_pages' => $doc->remaining_pages,
                    'visa_type' => $doc->visa_type,
                    'is_US_VISA' => $doc->is_US_VISA,
                    'file_path' => $doc->file_path,
                    'file_ext' => $doc->file_ext,
                    'created_at' => $doc->created_at,
                    'updated_at' => $doc->updated_at,
                    'modified_by' => $doc->modified_by,

                    // Nested relationships
                    'userProfile' => $doc->relationLoaded('userProfile') ? [
                        'id' => $doc->userProfile->id,
                        'user_id' => $doc->userProfile->user_id,
                        'crew_id' => $doc->userProfile->crew_id,
                        'first_name' => $doc->userProfile->first_name,
                        'middle_name' => $doc->userProfile->middle_name,
                        'last_name' => $doc->userProfile->last_name,
                        'suffix' => $doc->userProfile->suffix,
                        'birth_date' => $doc->userProfile->birth_date,
                        'birth_place' => $doc->userProfile->birth_place,
                        'age' => $doc->userProfile->age,
                        'gender' => $doc->userProfile->gender,
                        'nationality' => $doc->userProfile->nationality,
                        'civil_status' => $doc->userProfile->civil_status,
                        'religion' => $doc->userProfile->religion,
                        'blood_type' => $doc->userProfile->blood_type,
                        'created_at' => $doc->userProfile->created_at,
                        'updated_at' => $doc->userProfile->updated_at,
                    ] : null,

                    'travelDocumentType' => $doc->relationLoaded('travelDocumentType') ? [
                        'id' => $doc->travelDocumentType->id,
                        'name' => $doc->travelDocumentType->name,
                        'description' => $doc->travelDocumentType->description,
                        'icon' => $doc->travelDocumentType->icon,
                        'created_at' => $doc->travelDocumentType->created_at,
                        'updated_at' => $doc->travelDocumentType->updated_at,
                    ] : null,
                ];
            }),

            'userProfile' => $this->whenLoaded('userProfile', function () {
                return [
                    'id' => $this->userProfile->id,
                    'user_id' => $this->userProfile->user_id,
                    'crew_id' => $this->userProfile->crew_id,
                    'first_name' => $this->userProfile->first_name,
                    'middle_name' => $this->userProfile->middle_name,
                    'last_name' => $this->userProfile->last_name,
                    'suffix' => $this->userProfile->suffix,
                    'birth_date' => $this->userProfile->birth_date,
                    'birth_place' => $this->userProfile->birth_place,
                    'age' => $this->userProfile->age,
                    'gender' => $this->userProfile->gender,
                    'nationality' => $this->userProfile->nationality,
                    'civil_status' => $this->userProfile->civil_status,
                    'religion' => $this->userProfile->religion,
                    'blood_type' => $this->userProfile->blood_type,
                    'created_at' => $this->userProfile->created_at,
                    'updated_at' => $this->userProfile->updated_at,
                ];
            }),
        ];
    }
}
