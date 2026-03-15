<?php

namespace App\Modules\Appointments\Requests\Admin;

use App\Models\Appointment\Appointment;
use App\Modules\Appointments\Rules\DoctorMatchesSpecialty;
use App\Modules\Appointments\Rules\ServiceMatchesSpecialty;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('sanctum')->check();
    }

    public function rules(): array
    {
        $appointmentId = (int) $this->route('id');
        $appointment = Appointment::query()->find($appointmentId);
        $specialtyId = $this->has('specialty_id')
            ? $this->integer('specialty_id')
            : $appointment?->specialty_id;

        return [
            'specialty_id' => ['sometimes', 'required', 'integer', 'exists:medical_specialties,id'],
            'service_id' => ['sometimes', 'required', 'integer', 'exists:services,id', new ServiceMatchesSpecialty($specialtyId)],
            'doctor_id' => ['sometimes', 'required', 'integer', 'exists:users,id', new DoctorMatchesSpecialty($specialtyId)],
            'appointment_date' => ['sometimes', 'required', 'date', 'date_format:Y-m-d'],
            'appointment_time' => ['sometimes', 'required', 'date_format:H:i'],
            'status' => ['sometimes', 'required', 'string'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'specialty_id.exists' => 'Selected specialty does not exist',
            'service_id.exists' => 'Selected service does not exist',
            'doctor_id.exists' => 'Selected doctor does not exist',
        ];
    }
}
