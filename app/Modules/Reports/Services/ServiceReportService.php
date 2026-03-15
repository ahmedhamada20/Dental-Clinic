<?php

namespace App\Modules\Reports\Services;

use App\Models\Appointment\Appointment;
use App\Models\Clinic\Service;
use App\Modules\Reports\DTOs\ReportFilterDTO;
use App\Modules\Reports\Services\Concerns\InteractsWithReportDates;
use Illuminate\Support\Facades\DB;

class ServiceReportService
{
    use InteractsWithReportDates;

    public function generate(ReportFilterDTO $dto): array
    {
        [$from, $to] = $this->normalizeDateRange($dto);

        $serviceQuery = Service::query()
            ->when($dto->serviceId, fn ($query) => $query->whereKey($dto->serviceId));

        $appointmentBase = Appointment::query()
            ->whereBetween('appointment_date', [$from->toDateString(), $to->toDateString()])
            ->when($dto->serviceId, fn ($query) => $query->where('service_id', $dto->serviceId))
            ->when($dto->doctorId, fn ($query) => $query->where('assigned_doctor_id', $dto->doctorId));

        $rows = $serviceQuery
            ->leftJoin('appointments', function ($join) use ($from, $to, $dto) {
                $join->on('appointments.service_id', '=', 'services.id')
                    ->whereBetween('appointments.appointment_date', [$from->toDateString(), $to->toDateString()]);

                if ($dto->doctorId) {
                    $join->where('appointments.assigned_doctor_id', '=', $dto->doctorId);
                }
            })
            ->leftJoin('invoice_items', 'invoice_items.service_id', '=', 'services.id')
            ->leftJoin('invoices', function ($join) use ($from, $to) {
                $join->on('invoices.id', '=', 'invoice_items.invoice_id')
                    ->whereBetween('invoices.issued_at', [$from, $to]);
            })
            ->select([
                'services.id',
                'services.code',
                'services.name_en',
                'services.is_active',
                'services.is_bookable',
                DB::raw('COUNT(DISTINCT appointments.id) as appointments_count'),
                DB::raw('COALESCE(SUM(invoice_items.quantity), 0) as units_sold'),
                DB::raw('COALESCE(SUM(invoice_items.total), 0) as gross_revenue'),
                DB::raw('COALESCE(SUM(invoices.paid_amount), 0) as collected_amount'),
                DB::raw('COALESCE(SUM(invoices.remaining_amount), 0) as outstanding_amount'),
            ])
            ->groupBy('services.id', 'services.code', 'services.name_en', 'services.is_active', 'services.is_bookable')
            ->orderByDesc('gross_revenue')
            ->get()
            ->map(function ($row) {
                return [
                    'service_id' => (int) $row->id,
                    'code' => $row->code,
                    'service_name' => $row->name_en,
                    'is_active' => (bool) $row->is_active,
                    'is_bookable' => (bool) $row->is_bookable,
                    'appointments_count' => (int) $row->appointments_count,
                    'units_sold' => round((float) $row->units_sold, 2),
                    'gross_revenue' => round((float) $row->gross_revenue, 2),
                    'collected_amount' => round((float) $row->collected_amount, 2),
                    'outstanding_amount' => round((float) $row->outstanding_amount, 2),
                    'collection_rate' => $this->percentage((float) $row->collected_amount, (float) $row->gross_revenue),
                ];
            })
            ->values()
            ->all();

        return [
            'title' => 'Service Performance Report',
            'filters' => $dto->toArray(),
            'summary' => [
                'total_services' => (clone $serviceQuery)->count(),
                'active_services' => (clone $serviceQuery)->where('is_active', true)->count(),
                'bookable_services' => (clone $serviceQuery)->where('is_bookable', true)->count(),
                'appointments_count' => (clone $appointmentBase)->count(),
                'top_service' => $rows[0]['service_name'] ?? null,
                'total_gross_revenue' => round(array_sum(array_column($rows, 'gross_revenue')), 2),
                'total_collected_amount' => round(array_sum(array_column($rows, 'collected_amount')), 2),
            ],
            'rows' => $rows,
        ];
    }
}
