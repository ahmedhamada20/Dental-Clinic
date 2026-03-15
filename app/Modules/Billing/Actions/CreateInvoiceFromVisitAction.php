<?php

namespace App\Modules\Billing\Actions;

use App\Enums\InvoiceStatus;
use App\Models\Billing\Invoice;
use App\Models\Visit\Visit;
use App\Modules\Billing\Services\InvoiceNumberGeneratorService;
use Illuminate\Support\Facades\DB;

/**
 * Action for creating an invoice from a visit
 */
class CreateInvoiceFromVisitAction
{
    public function __construct(
        private InvoiceNumberGeneratorService $invoiceNumberGenerator,
    ) {}

    /**
     * Create invoice from visit
     *
     * @throws \Exception
     */
    public function __invoke(
        int $patientId,
        int $createdBy,
        ?int $visitId = null,
        ?int $promotionId = null,
        ?string $notes = null
    ): Invoice {
        return DB::transaction(function () use ($patientId, $createdBy, $visitId, $promotionId, $notes) {
            // Validate visit if provided
            if ($visitId) {
                $visit = Visit::findOrFail($visitId);
                if ($visit->patient_id !== $patientId) {
                    throw new \Exception('Visit does not belong to the specified patient');
                }
            }

            // Create invoice record
            $invoice = Invoice::create([
                'invoice_no' => $this->invoiceNumberGenerator->generate(),
                'patient_id' => $patientId,
                'visit_id' => $visitId,
                'created_by' => $createdBy,
                'subtotal' => 0,
                'discount_amount' => 0,
                'total' => 0,
                'paid_amount' => 0,
                'remaining_amount' => 0,
                'status' => InvoiceStatus::UNPAID,
                'promotion_id' => $promotionId,
                'notes' => $notes,
                'issued_at' => now(),
            ]);

            return $invoice->fresh(['items', 'patient', 'promotion']);
        });
    }
}
