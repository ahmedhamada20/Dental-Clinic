<?php
namespace App\Modules\Medical\Requests;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOdontogramToothRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'tooth_number' => ['required', 'integer', 'min:1', 'max:99'],
            'status' => ['required', 'string'],
            'surface' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'visit_id' => ['nullable', 'integer', 'exists:visits,id'],
        ];
    }
}
