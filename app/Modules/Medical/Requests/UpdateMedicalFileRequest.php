<?php
namespace App\Modules\Medical\Requests;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMedicalFileRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'file_category' => ['sometimes', 'nullable', 'string'],
            'visit_id' => ['sometimes', 'nullable', 'integer', 'exists:visits,id'],
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'is_visible_to_patient' => ['sometimes', 'nullable', 'boolean'],
        ];
    }
}
