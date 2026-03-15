<?php

namespace App\Modules\Reports\Services;

use App\Models\Billing\Invoice;
use App\Models\Billing\Payment;
use App\Modules\Reports\DTOs\ReportFilterDTO;
use App\Modules\Reports\Services\Concerns\InteractsWithReportDates;
use Illuminate\Support\Facades\DB;

class RevenueReportService
{
    use InteractsWithReportDates;

    public function generate(ReportFilterDTO $dto): array
    {
        [$from, $to] = $this->normalizeDateRange($dto);
        $groupBy = $dto->groupBy ?? 'day';

        $paymentQuery = Payment::query()
            ->with(['patient:id,full_name', 'invoice:id,invoice_no'])
            ->whereBetween('payment_date', [$from, $to]);

        $invoiceQuery = Invoice::query()
            ->whereBetween('issued_at', [$from, $to])
            ->when($dto->doctorId, fn ($query) => $query->whereHas('visit', fn ($visit) => $visit->where('doctor_id', $dto->doctorId)))
            ->when($dto->serviceId, fn ($query) => $query->whereHas('items', fn ($item) => $item->where('service_id', $dto->serviceId)));

        $trendRows = (clone $paymentQuery)
            ->selectRaw($this->trendExpression('payment_date', $groupBy) . ' as period')
            ->selectRaw('SUM(amount) as value')
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->map(fn ($row) => ['period' => $row->period, 'value' => (float) $row->value]);

        $rows = (clone $paymentQuery)
            ->latest('payment_date')
            ->limit(250)
            ->get()
            ->map(function (Payment $payment) {
                return [
                    'payment_id' => $payment->id,
                    'payment_no' => $payment->payment_no,
                    'payment_date' => optional($payment->payment_date)->format('Y-m-d H:i:s'),
                    'patient_name' => $payment->patient?->full_name,
                    'invoice_no' => $payment->invoice?->invoice_no,
                    'payment_method' => $payment->payment_method?->value ?? $payment->payment_method,
                    'amount' => round((float) $payment->amount, 2),
                    'reference_no' => $payment->reference_no,
                ];
            })
            ->values()
            ->all();

        $methodBreakdown = (clone $paymentQuery)
            ->select('payment_method', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(*) as payments_count'))
            ->groupBy('payment_method')
            ->orderByDesc('total_amount')
            ->get()
            ->map(fn ($row) => [
                'payment_method' => $row->payment_method instanceof \BackedEnum ? $row->payment_method->value : (string) $row->payment_method,
                'total_amount' => round((float) $row->total_amount, 2),
                'payments_count' => (int) $row->payments_count,
            ])
            ->values()
            ->all();

        $issuedAmount = round((float) (clone $invoiceQuery)->sum('total'), 2);
        $collectedAmount = round((float) (clone $paymentQuery)->sum('amount'), 2);

        return [
            'title' => 'Revenue Collection Analytics Report',
            'filters' => $dto->toArray(),
            'summary' => [
                'total_revenue_collected' => $collectedAmount,
                'payments_count' => (clone $paymentQuery)->count(),
                'average_payment_amount' => round((float) (clone $paymentQuery)->avg('amount'), 2),
                'issued_invoice_amount' => $issuedAmount,
                'collected_vs_issued_rate' => $this->percentage($collectedAmount, $issuedAmount),
                'outstanding_invoice_amount' => round((float) (clone $invoiceQuery)->sum('remaining_amount'), 2),
            ],
            'rows' => $rows,
            'analytics' => [
                'trend' => $this->buildTrend($trendRows, $from, $to, $groupBy),
                'payment_methods' => $methodBreakdown,
            ],
        ];
    }
}
