<?php

namespace App\Modules\Billing\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddInvoiceItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_id' => 'nullable|integer|exists:services,id',
            'treatment_plan_item_id' => 'nullable|integer|exists:treatment_plan_items,id',
            'item_type' => ['nullable', Rule::in(['service', 'manual', 'treatment_session'])],
            'item_name_ar' => 'nullable|string|max:255',
            'item_name_en' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'quantity' => 'required|numeric|min:0.01',
            'unit_price' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'tooth_number' => 'nullable|string|max:10',
        ];
    }

    protected function prepareForValidation(): void
    {
        $normalizedType = match ((string) $this->input('item_type')) {
            'treatment' => 'treatment_session',
            'adjustment' => 'manual',
            default => $this->input('item_type', null),
        };

        if ($this->filled('service_id') && ! filled($normalizedType)) {
            $normalizedType = 'service';
        }

        $this->merge([
            'item_type' => $normalizedType,
        ]);
    }

    public function messages(): array
    {
        return [
            'item_type.in' => 'Item type must be service, manual, or treatment session',
            'quantity.min' => 'Quantity must be greater than 0',
        ];
    }
}

