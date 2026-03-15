<?php

namespace App\Modules\Billing\Actions;

use App\Enums\InvoiceStatus;
use App\Models\Billing\Invoice;
use App\Modules\Billing\Services\PaymentService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Action for recording payments on an invoice
 */
class RecordPaymentAction
{
    public function __construct(
        private PaymentService $paymentService,
    ) {}

    /**
     * Record payment(s) for an invoice supporting mixed payment methods
     */
    public function __invoke(Invoice $invoice, array $payments, int $receivedBy): Collection
    {
        return DB::transaction(function () use ($invoice, $payments, $receivedBy) {
            if (in_array($invoice->status, [InvoiceStatus::CANCELLED, InvoiceStatus::PAID], true)) {
                throw new \RuntimeException('Payments can only be recorded against active unpaid invoices');
            }

            $totalAmount = (float) collect($payments)->sum('amount');
            if ($totalAmount <= 0) {
                throw new \InvalidArgumentException('Total payment amount must be greater than 0');
            }

            // Record payments
            $createdPayments = $this->paymentService->recordPayments(
                invoice: $invoice,
                payments: $payments,
                receivedBy: $receivedBy
            );

            return $createdPayments;
        });
    }
}
