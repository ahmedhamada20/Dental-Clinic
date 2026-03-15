<?php

namespace App\Modules\Billing\Services;

use App\Enums\InvoiceStatus;
use App\Models\Billing\Invoice;
use App\Models\Billing\Payment;
use Illuminate\Database\Eloquent\Collection;

/**
 * Service for managing payments and payment allocations
 */
class PaymentService
{
    /**
     * Record payment(s) for an invoice with support for mixed payments
     */
    public function recordPayments(Invoice $invoice, array $payments, int $receivedBy): Collection
    {
        $createdPayments = new Collection();
        $totalPaymentAmount = round((float) collect($payments)->sum('amount'), 2);
        $remainingBeforePayment = max(0, round((float) $invoice->total - (float) $invoice->paid_amount, 2));

        if ($totalPaymentAmount > $remainingBeforePayment) {
            throw new \InvalidArgumentException('Payment total exceeds invoice remaining amount');
        }

        // Create individual payment records for each payment method
        foreach ($payments as $paymentData) {
            $payment = Payment::create([
                'payment_no' => $this->generatePaymentNumber(),
                'patient_id' => $invoice->patient_id,
                'invoice_id' => $invoice->id,
                'received_by' => $receivedBy,
                'payment_method' => $paymentData['payment_method'],
                'amount' => round((float) $paymentData['amount'], 2),
                'reference_no' => $paymentData['reference_no'] ?? null,
                'payment_date' => $paymentData['payment_date'] ?? now(),
                'notes' => $paymentData['notes'] ?? null,
            ]);

            $createdPayments->push($payment);
        }

        $paidAmount = round((float) $invoice->paid_amount + $totalPaymentAmount, 2);
        $remainingAmount = max(0, round((float) $invoice->total - $paidAmount, 2));

        // Update invoice
        $invoice->update([
            'paid_amount' => $paidAmount,
            'remaining_amount' => $remainingAmount,
            'status' => $remainingAmount <= 0
                ? InvoiceStatus::PAID
                : ($paidAmount > 0 ? InvoiceStatus::PARTIALLY_PAID : InvoiceStatus::UNPAID),
        ]);

        return $createdPayments;
    }

    /**
     * Generate a unique payment number
     */
    public function generatePaymentNumber(): string
    {
        $date = now();
        $dateString = $date->format('Ymd');

        $todayCount = Payment::query()->whereDate('created_at', $date->toDateString())
            ->count();

        $sequence = str_pad($todayCount + 1, 4, '0', STR_PAD_LEFT);

        return "PAY-{$dateString}-{$sequence}";
    }

    /**
     * Get total payment for an invoice
     */
    public function getInvoicePaymentTotal(Invoice $invoice): float
    {
        return (float)$invoice->payments()->sum('amount');
    }

    /**
     * Get payment summary for an invoice
     */
    public function getPaymentSummary(Invoice $invoice): array
    {
        return [
            'total_amount' => (float)$invoice->total,
            'paid_amount' => (float)$invoice->paid_amount,
            'remaining_amount' => (float)$invoice->remaining_amount,
            'payment_count' => $invoice->payments()->count(),
            'status' => $invoice->status->value,
        ];
    }
}

