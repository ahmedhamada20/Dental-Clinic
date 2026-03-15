<?php

namespace App\Jobs;

use App\Enums\InvoiceStatus;
use App\Enums\NotificationType;
use App\Models\Billing\Invoice;
use App\Modules\Notifications\Services\NotificationDispatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * SendBillingDueReminderJob
 *
 * Sends billing due reminders.
 * Dispatched daily (via schedule) for overdue/partially-paid invoices,
 * or manually for a specific invoice.
 */
class SendBillingDueReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;

    /**
     * @param  int|null  $invoiceId  If null, processes all overdue invoices.
     * @param  string[]  $channels
     * @param  int|null  $triggeredBy
     */
    public function __construct(
        public readonly ?int  $invoiceId   = null,
        public readonly array $channels    = ['database', 'email'],
        public readonly ?int  $triggeredBy = null,
    ) {}

    public function handle(NotificationDispatcher $dispatcher): void
    {
        $invoices = $this->invoiceId
            ? Invoice::where('id', $this->invoiceId)->with('patient')->get()
            : Invoice::query()
                ->whereIn('status', [
                    InvoiceStatus::UNPAID->value,
                    InvoiceStatus::PARTIALLY_PAID->value,
                ])
                ->where('remaining_amount', '>', 0)
                ->with('patient')
                ->get();

        foreach ($invoices as $invoice) {
            $patient = $invoice->patient;
            if (! $patient) {
                continue;
            }

            $remaining = number_format((float) $invoice->remaining_amount, 2);
            $invoiceNo = $invoice->invoice_no ?? "#{$invoice->id}";

            foreach ($this->channels as $channel) {
                try {
                    $dispatcher->dispatch(
                        patient:         $patient,
                        title:           'Payment Reminder',
                        body:            "Dear {$patient->full_name}, invoice {$invoiceNo} has an outstanding balance of {$remaining}. Please arrange payment at your earliest convenience.",
                        channel:         $channel,
                        type:            NotificationType::BILLING_DUE->value,
                        data:            ['invoice_id' => $invoice->id, 'remaining_amount' => $invoice->remaining_amount],
                        triggeredBy:     $this->triggeredBy,
                        triggeredByType: $this->triggeredBy ? 'manual' : 'scheduled',
                    );
                } catch (\Throwable $e) {
                    Log::error('SendBillingDueReminderJob error', [
                        'invoice_id' => $invoice->id,
                        'channel'    => $channel,
                        'error'      => $e->getMessage(),
                    ]);
                }
            }
        }
    }
}

