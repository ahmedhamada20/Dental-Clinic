<?php

namespace App\Modules\Billing\Actions;

use App\Models\Billing\Invoice;
use App\Models\Billing\InvoiceItem;
use App\Models\Clinic\Service;
use App\Modules\Billing\DTOs\CreateInvoiceItemData;
use App\Modules\Billing\Services\InvoiceCalculationService;
use Illuminate\Support\Facades\DB;

/**
 * Action for adding an item to an invoice
 */
class AddInvoiceItemAction
{
    public function __construct(
        private InvoiceCalculationService $calculationService,
    ) {}

    /**
     * Add item to invoice
     */
    public function __invoke(Invoice $invoice, CreateInvoiceItemData $data): InvoiceItem
    {
        return DB::transaction(function () use ($invoice, $data) {
            $service = $data->service_id ? Service::query()->find($data->service_id) : null;

            $itemType = $data->item_type ?: 'service';
            if (! in_array($itemType, ['service', 'manual', 'treatment_session'], true)) {
                $itemType = 'service';
            }

            $itemNameAr = trim($data->item_name_ar);
            $itemNameEn = trim($data->item_name_en);
            if ($service) {
                $itemNameAr = $itemNameAr !== '' ? $itemNameAr : (string) ($service->name_ar ?? 'Service');
                $itemNameEn = $itemNameEn !== '' ? $itemNameEn : (string) ($service->name_en ?? $service->name_ar ?? 'Service');
            }

            if ($itemNameAr === '') {
                $itemNameAr = 'Service';
            }

            $description = $data->description;
            if (($description === null || trim($description) === '') && $service) {
                $description = $service->description_en ?: $service->description_ar;
            }

            // Calculate item total
            $itemTotal = ($data->unit_price * $data->quantity) - ($data->discount_amount ?? 0);

            // Create item
            $item = InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'service_id' => $data->service_id,
                'treatment_plan_item_id' => $data->treatment_plan_item_id,
                'item_type' => $itemType,
                'item_name_ar' => $itemNameAr,
                'item_name_en' => $itemNameEn !== '' ? $itemNameEn : null,
                'description' => $description,
                'quantity' => $data->quantity,
                'unit_price' => $data->unit_price,
                'discount_amount' => $data->discount_amount,
                'total' => max(0, $itemTotal),
                'tooth_number' => $data->tooth_number,
            ]);

            // Recalculate invoice totals
            $totals = $this->calculationService->calculateTotals($invoice->fresh());
            $invoice->update([
                'subtotal' => $totals['subtotal'],
                'discount_amount' => $totals['discount_amount'],
                'total' => $totals['total'],
                'remaining_amount' => max(0, $totals['total'] - (float)$invoice->paid_amount),
            ]);

            return $item;
        });
    }
}

