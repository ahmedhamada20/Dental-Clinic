<?php

namespace App\Modules\Billing\Actions;

use App\Models\Billing\Invoice;
use App\Models\Billing\InvoiceItem;
use App\Modules\Billing\Services\InvoiceCalculationService;
use Illuminate\Support\Facades\DB;

/**
 * Action for deleting an invoice item
 */
class DeleteInvoiceItemAction
{
    public function __construct(
        private InvoiceCalculationService $calculationService,
    ) {}

    /**
     * Delete item from invoice and recalculate totals
     */
    public function __invoke(InvoiceItem $item): bool
    {
        return DB::transaction(function () use ($item) {
            $invoice = $item->invoice;

            // Delete the item
            $item->delete();

            // Recalculate invoice totals
            $totals = $this->calculationService->calculateTotals($invoice->fresh());
            $invoice->update([
                'subtotal' => $totals['subtotal'],
                'discount_amount' => $totals['discount_amount'],
                'total' => $totals['total'],
                'remaining_amount' => max(0, $totals['total'] - (float)$invoice->paid_amount),
            ]);

            return true;
        });
    }
}

