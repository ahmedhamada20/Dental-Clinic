<?php

namespace App\Modules\Billing\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $patientId = $this->integer('patient_id');

        return [
            'patient_id' => 'required|integer|exists:patients,id',
            'visit_id' => [
                'nullable',
                'integer',
                Rule::exists('visits', 'id')->where(fn ($query) => $query->where('patient_id', $patientId)),
            ],
            'promotion_id' => 'nullable|integer|exists:promotions,id',
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'patient_id.required' => 'Patient ID is required',
            'patient_id.exists' => 'Selected patient does not exist',
            'visit_id.exists' => 'Selected visit does not belong to the selected patient',
            'promotion_id.exists' => 'Selected promotion does not exist',
        ];
    }
}

