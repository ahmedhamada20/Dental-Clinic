<?php

namespace App\Modules\Billing\Actions;

use App\Enums\InvoiceStatus;
use App\Models\Billing\Invoice;
use Illuminate\Support\Facades\DB;

/**
 * Action for finalizing an invoice
 */
class FinalizeInvoiceAction
{
    /**
     * Finalize an invoice (move from draft to finalized)
     */
    public function __invoke(Invoice $invoice): Invoice
    {
        return DB::transaction(function () use ($invoice) {
            if (in_array($invoice->status, [InvoiceStatus::PAID, InvoiceStatus::CANCELLED], true)) {
                throw new \RuntimeException('Paid or cancelled invoices cannot be finalized');
            }

            // Ensure invoice has items
            if ($invoice->items()->count() === 0) {
                throw new \RuntimeException('Invoice must have at least one item');
            }

            // Update status
            $invoice->update([
                'status' => InvoiceStatus::UNPAID,
                'issued_at' => now(),
            ]);

            return $invoice->fresh(['items', 'patient', 'promotion', 'payments']);
        });
    }
}
