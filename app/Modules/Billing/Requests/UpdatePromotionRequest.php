<?php

namespace App\Modules\Billing\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePromotionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $promotionId = $this->route('id');

        return [
            'title_ar' => 'nullable|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'code' => "nullable|string|max:50|unique:promotions,code,{$promotionId}",
            'promotion_type' => 'nullable|string|in:fixed,percentage',
            'value' => 'nullable|numeric|min:0.01',
            'applies_once' => 'nullable|boolean',
            'starts_at' => 'nullable|date|after_or_equal:today',
            'ends_at' => 'nullable|date',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'code.unique' => 'This promotion code already exists',
            'promotion_type.in' => 'Promotion type must be fixed or percentage',
            'value.min' => 'Value must be greater than 0',
        ];
    }
}

