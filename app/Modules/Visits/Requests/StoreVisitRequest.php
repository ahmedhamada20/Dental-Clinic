<?php

namespace App\Modules\Visits\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'appointment_id' => ['required', 'integer', 'exists:appointments,id'],
            'notes' => ['nullable', 'string'],
            'diagnosis' => ['nullable', 'string'],
        ];
    }
}

