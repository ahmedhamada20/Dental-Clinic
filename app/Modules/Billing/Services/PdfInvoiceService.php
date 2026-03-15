<?php

namespace App\Modules\Billing\Services;

use App\Models\Billing\Invoice;

/**
 * Service for generating PDF invoices
 *
 * Skeleton implementation - to be expanded with actual PDF generation
 */
class PdfInvoiceService
{
    /**
     * Generate PDF for an invoice
     */
    public function generate(Invoice $invoice): string
    {
        // TODO: Implement with MPDF or Dompdf
        // For now, return placeholder path
        return $this->getPdfPath($invoice);
    }

    /**
     * Generate and download PDF for an invoice
     */
    public function download(Invoice $invoice)
    {
        // TODO: Implement PDF download response
        // return response()->download($this->generate($invoice));
    }

    /**
     * Generate and stream PDF
     */
    public function stream(Invoice $invoice)
    {
        // TODO: Implement PDF streaming
    }

    /**
     * Get PDF path for invoice
     */
    public function getPdfPath(Invoice $invoice): string
    {
        return storage_path("app/invoices/{$invoice->invoice_no}.pdf");
    }

    /**
     * Build invoice template data
     */
    public function buildTemplateData(Invoice $invoice): array
    {
        return [
            'invoice' => $invoice,
            'patient' => $invoice->patient,
            'items' => $invoice->items,
            'payments' => $invoice->payments,
            'createdBy' => $invoice->createdBy,
            'promotion' => $invoice->promotion,
            'subtotal' => (float)$invoice->subtotal,
            'discount_amount' => (float)$invoice->discount_amount,
            'total' => (float)$invoice->total,
            'paid_amount' => (float)$invoice->paid_amount,
            'remaining_amount' => (float)$invoice->remaining_amount,
        ];
    }

    /**
     * Validate invoice is ready for PDF generation
     */
    public function canGenerate(Invoice $invoice): bool
    {
        // Invoice must be finalized to generate PDF
        return $invoice->status->value === 'finalized' ||
               $invoice->status->value === 'paid' ||
               $invoice->status->value === 'partially_paid';
    }
}

