<?php

namespace App\Modules\Billing\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $patientId = $this->integer('patient_id');

        return [
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'visit_id' => [
                'nullable',
                'integer',
                Rule::exists('visits', 'id')->where(fn ($query) => $query->where('patient_id', $patientId)),
            ],
            'promotion_id' => 'nullable|integer|exists:promotions,id',
            'notes' => 'nullable|string|max:500',
            'discount_type' => ['nullable', Rule::in(['percent', 'fixed', 'promotion'])],
            'discount_value' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'patient_id.required' => 'Patient is required',
            'patient_id.exists' => 'Selected patient does not exist',
            'visit_id.exists' => 'Selected visit does not belong to the selected patient',
            'promotion_id.exists' => 'Selected promotion does not exist',
            'discount_type.in' => 'Discount type must be percent, fixed, or promotion',
        ];
    }
}
