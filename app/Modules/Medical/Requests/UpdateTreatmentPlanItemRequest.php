<?php
namespace App\Modules\Medical\Requests;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTreatmentPlanItemRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'service_id' => ['sometimes', 'nullable', 'integer', 'exists:services,id'],
            'tooth_number' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:99'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'session_no' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'estimated_cost' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'status' => ['sometimes', 'nullable', 'string'],
            'planned_date' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
