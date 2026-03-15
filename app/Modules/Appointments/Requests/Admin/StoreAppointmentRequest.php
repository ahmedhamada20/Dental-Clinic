<?php

namespace App\Modules\Appointments\Requests\Admin;

use App\Modules\Appointments\Rules\DoctorMatchesSpecialty;
use App\Modules\Appointments\Rules\ServiceMatchesSpecialty;
use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('sanctum')->check();
    }

    public function rules(): array
    {
        $specialtyId = $this->integer('specialty_id');

        return [
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'specialty_id' => ['required', 'integer', 'exists:medical_specialties,id'],
            'doctor_id' => ['required', 'integer', 'exists:users,id', new DoctorMatchesSpecialty($specialtyId)],
            'service_id' => ['required', 'integer', 'exists:services,id', new ServiceMatchesSpecialty($specialtyId)],
            'appointment_date' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:today'],
            'appointment_time' => ['required', 'date_format:H:i'],
            'status' => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'patient_id.exists' => 'Selected patient does not exist',
            'specialty_id.exists' => 'Selected specialty does not exist',
            'doctor_id.exists' => 'Selected doctor does not exist',
            'service_id.exists' => 'Selected service does not exist',
        ];
    }
}
