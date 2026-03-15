<?php

namespace App\Modules\Billing\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePromotionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title_ar' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:promotions,code',
            'promotion_type' => 'required|string|in:fixed,percentage',
            'value' => 'required|numeric|min:0.01',
            'applies_once' => 'nullable|boolean',
            'starts_at' => 'required|date|after_or_equal:today',
            'ends_at' => 'required|date|after:starts_at',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'title_ar.required' => 'Arabic title is required',
            'title_en.required' => 'English title is required',
            'code.unique' => 'This promotion code already exists',
            'promotion_type.in' => 'Promotion type must be fixed or percentage',
            'value.min' => 'Value must be greater than 0',
            'ends_at.after' => 'End date must be after start date',
        ];
    }
}

