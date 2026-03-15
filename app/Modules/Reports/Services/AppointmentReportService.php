<?php

namespace App\Modules\Reports\Services;

use App\Enums\AppointmentStatus;
use App\Models\Appointment\Appointment;
use App\Modules\Reports\DTOs\ReportFilterDTO;
use App\Modules\Reports\Services\Concerns\InteractsWithReportDates;
use Illuminate\Support\Facades\DB;

class AppointmentReportService
{
    use InteractsWithReportDates;

    public function generate(ReportFilterDTO $dto): array
    {
        [$from, $to] = $this->normalizeDateRange($dto);
        $groupBy = $dto->groupBy ?? 'day';

        $query = Appointment::query()
            ->with(['patient:id,full_name', 'doctor:id,full_name', 'service:id,name_en'])
            ->whereBetween('appointment_date', [$from->toDateString(), $to->toDateString()])
            ->when($dto->doctorId, fn ($q) => $q->where('assigned_doctor_id', $dto->doctorId))
            ->when($dto->serviceId, fn ($q) => $q->where('service_id', $dto->serviceId))
            ->when($dto->status, fn ($q) => $q->where('status', $dto->status));

        $cancellationStatuses = [
            AppointmentStatus::CANCELLED_BY_PATIENT->value,
            AppointmentStatus::CANCELLED_BY_ADMIN->value,
            'cancelled_by_clinic',
        ];

        $summary = [
            'total_appointments' => (clone $query)->count(),
            'completed_appointments' => (clone $query)->where('status', AppointmentStatus::COMPLETED->value)->count(),
            'cancelled_appointments' => (clone $query)->whereIn('status', $cancellationStatuses)->count(),
            'no_show_appointments' => (clone $query)->where('status', AppointmentStatus::NO_SHOW->value)->count(),
        ];

        $summary['completion_rate'] = $this->percentage($summary['completed_appointments'], $summary['total_appointments']);
        $summary['cancellation_rate'] = $this->percentage($summary['cancelled_appointments'], $summary['total_appointments']);
        $summary['no_show_rate'] = $this->percentage($summary['no_show_appointments'], $summary['total_appointments']);

        $trendRows = (clone $query)
            ->selectRaw($this->trendExpression('appointment_date', $groupBy) . ' as period')
            ->selectRaw('COUNT(*) as value')
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->map(fn ($row) => ['period' => $row->period, 'value' => (int) $row->value]);

        $rows = (clone $query)
            ->latest('appointment_date')
            ->limit(250)
            ->get()
            ->map(function (Appointment $appointment) {
                return [
                    'appointment_id' => $appointment->id,
                    'appointment_no' => $appointment->appointment_no,
                    'appointment_date' => optional($appointment->appointment_date)->format('Y-m-d'),
                    'patient_name' => $appointment->patient?->full_name,
                    'doctor_name' => $appointment->doctor?->full_name,
                    'service_name' => $appointment->service?->name_en,
                    'status' => $appointment->status?->value ?? $appointment->status,
                    'booking_source' => $appointment->booking_source?->value ?? $appointment->booking_source,
                    'start_time' => $appointment->start_time,
                    'end_time' => $appointment->end_time,
                ];
            })
            ->values()
            ->all();

        return [
            'title' => 'Appointment Analytics Report',
            'filters' => $dto->toArray(),
            'summary' => $summary,
            'rows' => $rows,
            'analytics' => [
                'trend' => $this->buildTrend($trendRows, $from, $to, $groupBy),
            ],
        ];
    }
}
