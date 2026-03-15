<?php

namespace App\Modules\Billing\Actions;

use App\Enums\DiscountType;
use App\Models\Billing\Invoice;
use App\Modules\Billing\DTOs\UpdateInvoiceData;
use App\Modules\Billing\Services\InvoiceCalculationService;
use Illuminate\Support\Facades\DB;

/**
 * Action for updating an invoice
 */
class UpdateInvoiceAction
{
    public function __construct(
        private InvoiceCalculationService $calculationService,
    ) {}

    /**
     * Update invoice details
     */
    public function __invoke(Invoice $invoice, UpdateInvoiceData $data): Invoice
    {
        return DB::transaction(function () use ($invoice, $data) {
            if ($data->patient_id !== null) {
                $invoice->patient_id = $data->patient_id;
            }
            $invoice->visit_id = $data->visit_id;
            $invoice->promotion_id = $data->promotion_id;
            $invoice->notes = $data->notes;
            $invoice->discount_type = $data->discount_type ? DiscountType::from($data->discount_type) : null;
            $invoice->discount_value = $data->discount_value;

            $totals = $this->calculationService->calculateTotals($invoice->fresh('items'));
            $invoice->subtotal = $totals['subtotal'];
            $invoice->discount_amount = $totals['discount_amount'];
            $invoice->total = $totals['total'];
            $invoice->remaining_amount = max(0, round($totals['total'] - (float) $invoice->paid_amount, 2));
            $invoice->save();

            return $invoice->fresh(['items', 'patient', 'promotion', 'payments', 'visit']);
        });
    }
}
