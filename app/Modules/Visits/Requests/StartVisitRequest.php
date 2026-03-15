<?php

namespace App\Modules\Visits\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StartVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'clinical_notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
