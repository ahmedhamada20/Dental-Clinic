<?php
namespace App\Modules\Medical\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreMedicalFileRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:10240'],
            'file_category' => ['required', 'string'],
            'visit_id' => ['nullable', 'integer', 'exists:visits,id'],
            'title' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'is_visible_to_patient' => ['nullable', 'boolean'],
        ];
    }
}
