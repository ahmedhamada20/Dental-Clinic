<?php
namespace App\Modules\Medical\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreTreatmentPlanItemRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'tooth_number' => ['nullable', 'integer', 'min:1', 'max:99'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'session_no' => ['nullable', 'integer', 'min:1'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string'],
            'planned_date' => ['nullable', 'date'],
        ];
    }
}
