<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobDescriptionRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'purpose' => ['required', 'in:SSS,PAG_IBIG,PHILHEALTH,VISA'],
            'visa_type' => ['required_if:purpose,VISA', 'nullable', 'in:TOURIST,BUSINESS,WORK,TRANSIT,STUDENT,FAMILY,SEAMAN'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'purpose.required' => 'Purpose of request is required.',
            'purpose.in' => 'Please select a valid purpose from the available options.',
            'visa_type.required_if' => 'VISA type is required when purpose is VISA application.',
            'visa_type.in' => 'Please select a valid VISA type from the available options.',
            'notes.max' => 'Additional notes cannot exceed 1000 characters.',
        ];
    }
}