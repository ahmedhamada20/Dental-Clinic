<?php

namespace App\Modules\Reports\Services;

use App\Models\Patient\Patient;
use App\Modules\Reports\DTOs\ReportFilterDTO;
use App\Modules\Reports\Services\Concerns\InteractsWithReportDates;
use Illuminate\Support\Facades\DB;

class PatientReportService
{
    use InteractsWithReportDates;

    public function generate(ReportFilterDTO $dto): array
    {
        [$from, $to] = $this->normalizeDateRange($dto);

        $patientQuery = Patient::query()
            ->withCount([
                'appointments as appointments_in_period_count' => fn ($query) => $query->whereBetween('appointment_date', [$from->toDateString(), $to->toDateString()]),
                'appointments as lifetime_appointments_count',
            ])
            ->with([
                'appointments' => fn ($query) => $query
                    ->whereBetween('appointment_date', [$from->toDateString(), $to->toDateString()])
                    ->orderBy('appointment_date'),
            ])
            ->whereHas('appointments', fn ($query) => $query->whereBetween('appointment_date', [$from->toDateString(), $to->toDateString()]));

        $rows = $patientQuery
            ->get()
            ->map(function (Patient $patient) use ($from) {
                $firstAppointment = $patient->appointments->min('appointment_date');
                $lastAppointment = $patient->appointments->max('appointment_date');
                $lifetimeAppointments = (int) $patient->lifetime_appointments_count;
                $appointmentsInPeriod = (int) $patient->appointments_in_period_count;
                $isRetained = $lifetimeAppointments > 1;
                $isNew = $firstAppointment && $firstAppointment->gte($from->copy()->startOfDay());

                return [
                    'patient_id' => $patient->id,
                    'patient_code' => $patient->patient_code,
                    'patient_name' => $patient->full_name,
                    'phone' => $patient->phone,
                    'status' => $patient->status?->value ?? $patient->status,
                    'appointments_in_period' => $appointmentsInPeriod,
                    'lifetime_appointments' => $lifetimeAppointments,
                    'first_appointment_in_period' => optional($firstAppointment)->format('Y-m-d'),
                    'last_appointment_in_period' => optional($lastAppointment)->format('Y-m-d'),
                    'is_new_patient' => $isNew,
                    'is_retained_patient' => $isRetained,
                ];
            })
            ->sortByDesc('appointments_in_period')
            ->values()
            ->all();

        $newPatients = count(array_filter($rows, fn (array $row) => $row['is_new_patient']));
        $retainedPatients = count(array_filter($rows, fn (array $row) => $row['is_retained_patient']));
        $totalPatients = count($rows);

        return [
            'title' => 'Patient Retention Report',
            'filters' => $dto->toArray(),
            'summary' => [
                'patients_with_appointments' => $totalPatients,
                'new_patients' => $newPatients,
                'returning_patients' => max($totalPatients - $newPatients, 0),
                'retained_patients' => $retainedPatients,
                'retention_rate' => $this->percentage($retainedPatients, $totalPatients),
                'average_appointments_per_patient' => round($totalPatients ? array_sum(array_column($rows, 'appointments_in_period')) / $totalPatients : 0, 2),
            ],
            'rows' => $rows,
        ];
    }
}
