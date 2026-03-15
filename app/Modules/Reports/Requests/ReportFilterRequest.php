<?php

namespace App\Modules\Reports\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'doctor_id' => ['nullable', 'integer', 'exists:users,id'],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'group_by' => ['nullable', 'string', Rule::in(['day', 'week', 'month'])],
            'report_type' => ['nullable', 'string', Rule::in(['appointments', 'revenue', 'invoices', 'patients', 'services', 'promotions', 'doctors', 'audit_logs'])],
            'export_format' => ['nullable', 'string', Rule::in(['pdf', 'xlsx'])],
            'status' => ['nullable', 'string'],
            'invoice_status' => ['nullable', 'string'],
        ];
    }
}
