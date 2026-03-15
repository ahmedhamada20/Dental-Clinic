<?php
namespace App\Modules\Medical\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StorePrescriptionRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['notes' => ['nullable', 'string', 'max:5000']]; }
}
