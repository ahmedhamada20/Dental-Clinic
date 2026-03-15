<?php

namespace App\Modules\Billing\Requests;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class RecordPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payments' => 'required|array|min:1',
            'payments.*.payment_method' => ['required', new Enum(PaymentMethod::class)],
            'payments.*.amount' => 'required|numeric|min:0.01',
            'payments.*.payment_date' => 'nullable|date',
            'payments.*.reference_no' => 'nullable|string|max:100',
            'payments.*.notes' => 'nullable|string|max:300',
        ];
    }

    public function messages(): array
    {
        return [
            'payments.required' => 'At least one payment is required',
            'payments.min' => 'At least one payment entry is required',
            'payments.*.amount.required' => 'Amount is required for each payment',
            'payments.*.amount.min' => 'Amount must be greater than 0',
            'payments.*.payment_date.date' => 'Payment date must be a valid date',
        ];
    }
}
