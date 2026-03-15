<?php

namespace App\Modules\Reports\Services;

use App\Enums\AppointmentStatus;
use App\Enums\UserType;
use App\Models\Appointment\Appointment;
use App\Models\Billing\Invoice;
use App\Models\User;
use App\Modules\Reports\DTOs\ReportFilterDTO;
use App\Modules\Reports\Services\Concerns\InteractsWithReportDates;
use Illuminate\Support\Facades\DB;

class DoctorReportService
{
    use InteractsWithReportDates;

    public function generate(ReportFilterDTO $dto): array
    {
        [$from, $to] = $this->normalizeDateRange($dto);

        $doctorQuery = User::query()
            ->where('user_type', 'doctor')
            ->when($dto->doctorId, fn ($query) => $query->whereKey($dto->doctorId));

        $appointmentBase = Appointment::query()
            ->whereBetween('appointment_date', [$from->toDateString(), $to->toDateString()])
            ->when($dto->doctorId, fn ($query) => $query->where('assigned_doctor_id', $dto->doctorId))
            ->when($dto->serviceId, fn ($query) => $query->where('service_id', $dto->serviceId));

        $invoiceBase = Invoice::query()
            ->whereBetween('issued_at', [$from, $to])
            ->when($dto->doctorId, fn ($query) => $query->whereHas('visit', fn ($visit) => $visit->where('doctor_id', $dto->doctorId)))
            ->when($dto->serviceId, fn ($query) => $query->whereHas('items', fn ($item) => $item->where('service_id', $dto->serviceId)));

        $rows = $doctorQuery
            ->leftJoin('appointments', function ($join) use ($from, $to, $dto) {
                $join->on('appointments.assigned_doctor_id', '=', 'users.id')
                    ->whereBetween('appointments.appointment_date', [$from->toDateString(), $to->toDateString()]);

                if ($dto->serviceId) {
                    $join->where('appointments.service_id', '=', $dto->serviceId);
                }
            })
            ->leftJoin('visits', 'visits.doctor_id', '=', 'users.id')
            ->leftJoin('invoices', function ($join) use ($from, $to) {
                $join->on('invoices.visit_id', '=', 'visits.id')
                    ->whereBetween('invoices.issued_at', [$from, $to]);
            })
            ->select([
                'users.id',
                DB::raw("COALESCE(users.full_name, TRIM(CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')))) as doctor_name"),
                'users.email',
                'users.status',
                DB::raw('COUNT(DISTINCT appointments.id) as total_appointments'),
                DB::raw("SUM(CASE WHEN appointments.status = '" . AppointmentStatus::COMPLETED->value . "' THEN 1 ELSE 0 END) as completed_appointments"),
                DB::raw("SUM(CASE WHEN appointments.status IN ('" . AppointmentStatus::CANCELLED_BY_PATIENT->value . "', 'cancelled_by_clinic', '" . AppointmentStatus::CANCELLED_BY_ADMIN->value . "') THEN 1 ELSE 0 END) as cancelled_appointments"),
                DB::raw("SUM(CASE WHEN appointments.status = '" . AppointmentStatus::NO_SHOW->value . "' THEN 1 ELSE 0 END) as no_show_appointments"),
                DB::raw('COUNT(DISTINCT invoices.id) as invoices_count'),
                DB::raw('COALESCE(SUM(invoices.total), 0) as invoiced_amount'),
                DB::raw('COALESCE(SUM(invoices.paid_amount), 0) as collected_amount'),
                DB::raw('COALESCE(SUM(invoices.remaining_amount), 0) as outstanding_amount'),
            ])
            ->groupBy('users.id', 'users.full_name', 'users.first_name', 'users.last_name', 'users.email', 'users.status')
            ->orderByDesc('collected_amount')
            ->get()
            ->map(function ($row) {
                $totalAppointments = (int) $row->total_appointments;

                return [
                    'doctor_id' => (int) $row->id,
                    'doctor_name' => $row->doctor_name,
                    'email' => $row->email,
                    'status' => $row->status instanceof \BackedEnum ? $row->status->value : (string) $row->status,
                    'total_appointments' => $totalAppointments,
                    'completed_appointments' => (int) $row->completed_appointments,
                    'cancelled_appointments' => (int) $row->cancelled_appointments,
                    'no_show_appointments' => (int) $row->no_show_appointments,
                    'completion_rate' => $this->percentage((int) $row->completed_appointments, $totalAppointments),
                    'cancellation_rate' => $this->percentage((int) $row->cancelled_appointments, $totalAppointments),
                    'no_show_rate' => $this->percentage((int) $row->no_show_appointments, $totalAppointments),
                    'invoices_count' => (int) $row->invoices_count,
                    'invoiced_amount' => round((float) $row->invoiced_amount, 2),
                    'collected_amount' => round((float) $row->collected_amount, 2),
                    'outstanding_amount' => round((float) $row->outstanding_amount, 2),
                    'collection_rate' => $this->percentage((float) $row->collected_amount, max((float) $row->invoiced_amount, 0.0)),
                ];
            })
            ->values()
            ->all();

        $summary = [
            'total_doctors' => (clone $doctorQuery)->count(),
            'active_doctors_in_period' => count(array_filter($rows, fn (array $row) => $row['total_appointments'] > 0 || $row['invoices_count'] > 0)),
            'total_appointments' => (clone $appointmentBase)->count(),
            'completed_appointments' => (clone $appointmentBase)->where('status', AppointmentStatus::COMPLETED->value)->count(),
            'cancelled_appointments' => (clone $appointmentBase)->whereIn('status', [AppointmentStatus::CANCELLED_BY_PATIENT->value, 'cancelled_by_clinic', AppointmentStatus::CANCELLED_BY_ADMIN->value])->count(),
            'no_show_appointments' => (clone $appointmentBase)->where('status', AppointmentStatus::NO_SHOW->value)->count(),
            'total_invoiced_amount' => round((float) (clone $invoiceBase)->sum('total'), 2),
            'total_collected_amount' => round((float) (clone $invoiceBase)->sum('paid_amount'), 2),
            'top_doctor' => $rows[0]['doctor_name'] ?? null,
        ];

        return [
            'title' => 'Doctor Performance Report',
            'filters' => $dto->toArray(),
            'summary' => $summary,
            'rows' => $rows,
        ];
    }
}
