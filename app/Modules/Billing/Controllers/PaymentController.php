<?php

namespace App\Modules\Billing\Controllers;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Concerns\AppliesSpecialtyScope;
use App\Http\Controllers\Controller;
use App\Models\Billing\Invoice;
use App\Models\Billing\Payment;
use App\Models\Patient\Patient;
use App\Modules\Audit\Services\AuditLogService;
use App\Modules\Billing\Actions\DeletePaymentAction;
use App\Modules\Billing\Actions\RecordPaymentAction;
use App\Modules\Billing\Requests\RecordPaymentRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    use AppliesSpecialtyScope;

    public function __construct(
        private RecordPaymentAction $recordPaymentAction,
        private DeletePaymentAction $deletePaymentAction,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function index(Request $request): View
    {
        $query = $this->scopePayments(Payment::query()->with(['patient', 'invoice', 'receivedBy']));

        if ($request->filled('invoice_id')) {
            $query->where('invoice_id', $request->integer('invoice_id'));
        }

        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->integer('patient_id'));
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->string('payment_method'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($builder) use ($search) {
                $builder->where('payment_no', 'like', "%{$search}%")
                    ->orWhere('reference_no', 'like', "%{$search}%")
                    ->orWhereHas('invoice', fn ($invoiceQuery) => $invoiceQuery->where('invoice_no', 'like', "%{$search}%"))
                    ->orWhereHas('patient', function ($patientQuery) use ($search) {
                        $patientQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('full_name', 'like', "%{$search}%");
                    });
            });
        }

        $payments = $query->latest('payment_date')->paginate(20)->withQueryString();
        $statsQuery = $this->scopePayments(Payment::query());

        return view('admin.billing.payments', [
            'payments' => $payments,
            'statistics' => [
                'total' => (clone $statsQuery)->count(),
                'totalAmount' => (float) (clone $statsQuery)->sum('amount'),
                'todayAmount' => (float) (clone $statsQuery)->whereDate('payment_date', today())->sum('amount'),
                'monthAmount' => (float) (clone $statsQuery)->whereYear('payment_date', now()->year)->whereMonth('payment_date', now()->month)->sum('amount'),
                'cashAmount' => (float) (clone $statsQuery)->where('payment_method', PaymentMethod::CASH->value)->sum('amount'),
                'cardAmount' => 0.0,
                'otherAmount' => (float) (clone $statsQuery)->whereNotIn('payment_method', [PaymentMethod::CASH->value])->sum('amount'),
            ],
            'patients' => $this->scopePatients(Patient::query())->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'full_name']),
            'paymentMethods' => PaymentMethod::cases(),
        ]);
    }

    public function show(Payment $payment): View
    {
        $this->ensureCanAccessPayment($payment);

        $payment->load(['patient', 'invoice', 'receivedBy']);

        return view('admin.billing.payment-show', [
            'payment' => $payment,
        ]);
    }

    public function store(RecordPaymentRequest $request, Invoice $invoice): RedirectResponse
    {
        $this->ensureCanAccessInvoice($invoice);

        try {
            ($this->recordPaymentAction)($invoice, $request->validated('payments'), (int) auth()->id());
        } catch (\InvalidArgumentException|\RuntimeException $exception) {
            return back()
                ->withInput()
                ->withErrors(['payments' => $exception->getMessage()]);
        }

        $this->auditLogService->log('billing', 'billing_action', $invoice->fresh() ?? $invoice, null, [
            'operation' => 'payment_recorded',
            'payments' => $request->validated('payments'),
        ]);

        return redirect()
            ->route('admin.billing.invoices.show', $invoice)
            ->with('success', __('admin.billing.payment_recorded'));
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        $this->ensureCanAccessPayment($payment);

        $invoice = $payment->invoice;
        $before = $payment->toArray();
        ($this->deletePaymentAction)($payment);

        $this->auditLogService->log('billing', 'billing_action', $invoice, [
            'operation' => 'payment_deleted',
            'payment' => $before,
        ], null);

        return redirect()
            ->route('admin.billing.invoices.show', $invoice)
            ->with('success', __('admin.billing.payment_deleted'));
    }
}

