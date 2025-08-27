<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContractRequest extends FormRequest
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
            'contract_number' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
            'vessel_id' => 'required|exists:vessels,id',
            'departure_date' => 'nullable|date',
            'arrival_date' => 'nullable|date',
            'duration_months' => 'nullable|integer|min:1|max:120',
            'contract_start_date' => 'required|date',
            'contract_end_date' => 'nullable|date|after:contract_start_date',
        ];
    }
}
