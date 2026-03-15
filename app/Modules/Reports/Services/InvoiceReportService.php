<?php

namespace App\Modules\Reports\Services;

use App\Models\Billing\Invoice;
use App\Modules\Reports\DTOs\ReportFilterDTO;
use App\Modules\Reports\Services\Concerns\InteractsWithReportDates;
use Illuminate\Support\Facades\DB;

class InvoiceReportService
{
    use InteractsWithReportDates;

    public function generate(ReportFilterDTO $dto): array
    {
        [$from, $to] = $this->normalizeDateRange($dto);

        $query = Invoice::query()
            ->with(['patient:id,full_name', 'visit:id,doctor_id', 'visit.doctor:id,full_name'])
            ->whereBetween('issued_at', [$from, $to])
            ->when($dto->invoiceStatus, fn ($q) => $q->where('status', $dto->invoiceStatus))
            ->when($dto->doctorId, fn ($q) => $q->whereHas('visit', fn ($visit) => $visit->where('doctor_id', $dto->doctorId)))
            ->when($dto->serviceId, fn ($q) => $q->whereHas('items', fn ($item) => $item->where('service_id', $dto->serviceId)));

        $rows = (clone $query)
            ->latest('issued_at')
            ->limit(250)
            ->get()
            ->map(function (Invoice $invoice) {
                return [
                    'invoice_id' => $invoice->id,
                    'invoice_no' => $invoice->invoice_no,
                    'issued_at' => optional($invoice->issued_at)->format('Y-m-d H:i:s'),
                    'patient_name' => $invoice->patient?->full_name,
                    'doctor_name' => $invoice->visit?->doctor?->full_name,
                    'status' => $invoice->status?->value ?? $invoice->status,
                    'total' => round((float) $invoice->total, 2),
                    'paid_amount' => round((float) $invoice->paid_amount, 2),
                    'remaining_amount' => round((float) $invoice->remaining_amount, 2),
                    'is_unpaid' => (float) $invoice->remaining_amount > 0,
                ];
            })
            ->values()
            ->all();

        $statusBreakdown = (clone $query)
            ->select('status', DB::raw('COUNT(*) as invoices_count'), DB::raw('SUM(total) as total_amount'), DB::raw('SUM(remaining_amount) as remaining_amount'))
            ->groupBy('status')
            ->orderByDesc('remaining_amount')
            ->get()
            ->map(fn ($row) => [
                'status' => $row->status instanceof \BackedEnum ? $row->status->value : (string) $row->status,
                'invoices_count' => (int) $row->invoices_count,
                'total_amount' => round((float) $row->total_amount, 2),
                'remaining_amount' => round((float) $row->remaining_amount, 2),
            ])
            ->values()
            ->all();

        $unpaidInvoices = array_values(array_filter($rows, fn (array $row) => $row['is_unpaid']));

        return [
            'title' => 'Invoice Analytics Report',
            'filters' => $dto->toArray(),
            'summary' => [
                'total_invoices' => (clone $query)->count(),
                'unpaid_invoices_count' => count($unpaidInvoices),
                'fully_paid_invoices_count' => (clone $query)->where('remaining_amount', '<=', 0)->count(),
                'total_amount' => round((float) (clone $query)->sum('total'), 2),
                'paid_amount' => round((float) (clone $query)->sum('paid_amount'), 2),
                'remaining_amount' => round((float) (clone $query)->sum('remaining_amount'), 2),
            ],
            'rows' => $rows,
            'analytics' => [
                'status_breakdown' => $statusBreakdown,
                'unpaid_invoices' => $unpaidInvoices,
            ],
        ];
    }
}
