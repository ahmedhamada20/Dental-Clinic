<?php

namespace App\Modules\Dashboard\Services;

use App\Enums\InvoiceStatus;
use App\Enums\VisitStatus;
use App\Models\Appointment\Appointment;
use App\Models\Appointment\WaitingListRequest;
use App\Models\Billing\Invoice;
use App\Models\Billing\Payment;
use App\Models\Visit\Visit;

class DashboardSummaryService
{
    public function getSummary(): array
    {
        $today = now()->toDateString();

        return [
            'today_appointments' => Appointment::query()
                ->whereDate('appointment_date', $today)
                ->count(),
            'checked_in_count' => Visit::query()
                ->whereDate('visit_date', $today)
                ->whereIn('status', [
                    VisitStatus::CHECKED_IN->value,
                    VisitStatus::WITH_DOCTOR->value,
                    VisitStatus::SCHEDULED->value,
                    VisitStatus::IN_PROGRESS->value,
                ])
                ->count(),
            'completed_visits' => Visit::query()
                ->whereDate('visit_date', $today)
                ->where('status', 'completed')
                ->count(),
            'today_revenue' => (float) Payment::query()
                ->whereDate('paid_at', $today)
                ->sum('amount'),
            'pending_invoices' => Invoice::query()
                ->where('status', InvoiceStatus::UNPAID->value)
                ->count(),
            'waiting_list_count' => WaitingListRequest::query()
                ->where('status', \App\Enums\WaitingListStatus::PENDING->value)
                ->count(),
        ];
    }
}
