<?php

namespace App\Modules\Billing\Actions;

use App\Enums\InvoiceStatus;
use App\Models\Billing\Invoice;
use Illuminate\Support\Facades\DB;

/**
 * Action for cancelling an invoice
 */
class CancelInvoiceAction
{
    /**
     * Cancel an invoice and remove related payments
     */
    public function __invoke(Invoice $invoice, ?string $reason = null): Invoice
    {
        return DB::transaction(function () use ($invoice, $reason) {
            // Can't cancel already paid or cancelled invoices
            if (in_array($invoice->status, [InvoiceStatus::PAID, InvoiceStatus::CANCELLED], true)) {
                throw new \RuntimeException('Cannot cancel a paid or already cancelled invoice');
            }

            // Prefer safe reversal - delete all payments associated with this invoice
            $invoice->payments()->delete();

            // Update invoice status and reset payment tracking
            $invoice->update([
                'status' => InvoiceStatus::CANCELLED,
                'paid_amount' => 0,
                'remaining_amount' => round((float) $invoice->total, 2),
                'notes' => trim(($invoice->notes ? $invoice->notes."\n\n" : '').'Cancelled'.($reason ? ': '.$reason : '')),
            ]);

            return $invoice->fresh(['items', 'patient', 'promotion', 'payments']);
        });
    }
}
