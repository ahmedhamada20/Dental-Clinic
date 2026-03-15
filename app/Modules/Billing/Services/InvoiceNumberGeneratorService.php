<?php

namespace App\Modules\Billing\Services;

use App\Models\Billing\Invoice;
use Carbon\Carbon;

/**
 * Service for generating unique invoice numbers
 */
class InvoiceNumberGeneratorService
{
    /**
     * Generate a unique invoice number in format INV-YYYYMMDD-XXXX
     */
    public function generate(): string
    {
        $date = Carbon::now();
        $dateString = $date->format('Ymd');

        // Get count of invoices created today
        $todayCount = Invoice::whereDate('created_at', $date)
            ->count();

        $sequence = str_pad($todayCount + 1, 4, '0', STR_PAD_LEFT);

        return "INV-{$dateString}-{$sequence}";
    }

    /**
     * Generate sequential invoice number (fallback)
     */
    public function generateSequential(): string
    {
        $lastInvoice = Invoice::orderByDesc('id')
            ->first();

        $nextNumber = ($lastInvoice?->id ?? 0) + 1;
        $sequence = str_pad($nextNumber, 7, '0', STR_PAD_LEFT);

        return "INV-{$sequence}";
    }

    /**
     * Validate invoice number format
     */
    public function validate(string $invoiceNo): bool
    {
        return (bool)preg_match('/^INV-\d{8}-\d{4}$/', $invoiceNo) ||
               (bool)preg_match('/^INV-\d{7}$/', $invoiceNo);
    }
}

