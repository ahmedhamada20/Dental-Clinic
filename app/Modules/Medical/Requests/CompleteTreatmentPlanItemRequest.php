<?php
namespace App\Modules\Medical\Requests;
use Illuminate\Foundation\Http\FormRequest;

class CompleteTreatmentPlanItemRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'completed_visit_id' => ['nullable', 'integer', 'exists:visits,id'],
        ];
    }
}
