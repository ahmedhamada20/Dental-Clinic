<?php

namespace App\Modules\Appointments\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentDetailsResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'appointment_no' => $this->appointment_no,
            'patient_id' => $this->patient_id,
            'doctor_id' => $this->assigned_doctor_id,
            'specialty_id' => $this->specialty_id,
            'service_id' => $this->service_id,
            'patient' => [
                'id' => $this->patient->id,
                'patient_code' => $this->patient->patient_code,
                'full_name' => $this->patient->full_name,
                'phone' => $this->patient->phone,
                'email' => $this->patient->email,
            ],
            'specialty' => $this->specialty ? [
                'id' => $this->specialty->id,
                'name' => $this->specialty->name,
                'description' => $this->specialty->description,
            ] : null,
            'service' => $this->service ? [
                'id' => $this->service->id,
                'name_en' => $this->service->name_en,
                'name_ar' => $this->service->name_ar,
                'duration_minutes' => $this->service->duration_minutes,
                'default_price' => $this->service->default_price,
                'specialty_id' => $this->service->category?->medical_specialty_id,
            ] : null,
            'doctor' => $this->doctor ? [
                'id' => $this->doctor->id,
                'full_name' => $this->doctor->full_name,
                'specialty_id' => $this->doctor->specialty_id,
            ] : null,
            'appointment_date' => $this->appointment_date?->format('Y-m-d'),
            'appointment_time' => $this->start_time ? substr((string) $this->start_time, 0, 5) : null,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'status' => $this->status?->value,
            'booking_source' => $this->booking_source?->value,
            'notes' => $this->notes,
            'confirmed_at' => $this->confirmed_at?->format('Y-m-d H:i:s'),
            'checked_in_at' => $this->checked_in_at?->format('Y-m-d H:i:s'),
            'cancellation_reason' => $this->cancellation_reason,
            'cancelled_at' => $this->cancelled_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
