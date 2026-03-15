<?php

namespace App\Modules\Billing\Actions;

use App\Enums\InvoiceStatus;
use App\Models\Billing\Payment;
use Illuminate\Support\Facades\DB;

/**
 * Action for deleting a payment (safe reversal pattern)
 */
class DeletePaymentAction
{
    /**
     * Delete a payment and update invoice status/totals
     */
    public function __invoke(Payment $payment): bool
    {
        return DB::transaction(function () use ($payment) {
            $invoice = $payment->invoice;
            $paymentAmount = (float) $payment->amount;

            $newPaidAmount = max(0, round((float) $invoice->paid_amount - $paymentAmount, 2));
            $newRemainingAmount = max(0, round((float) $invoice->total - $newPaidAmount, 2));

            $newStatus = $newPaidAmount <= 0
                ? InvoiceStatus::UNPAID
                : ($newRemainingAmount <= 0 ? InvoiceStatus::PAID : InvoiceStatus::PARTIALLY_PAID);

            $invoice->update([
                'paid_amount' => $newPaidAmount,
                'remaining_amount' => $newRemainingAmount,
                'status' => $newStatus,
            ]);

            $payment->delete();

            return true;
        });
    }
}
