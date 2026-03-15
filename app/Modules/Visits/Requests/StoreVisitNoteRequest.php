<?php

namespace App\Modules\Visits\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVisitNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'note_type' => ['required', 'string', 'max:50'],
            'note' => ['required', 'string', 'max:5000'],
        ];
    }
}
