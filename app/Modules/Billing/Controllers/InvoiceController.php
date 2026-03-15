<?php

namespace App\Modules\Billing\Controllers;

use App\Enums\DiscountType;
use App\Enums\InvoiceStatus;
use App\Http\Controllers\Concerns\AppliesSpecialtyScope;
use App\Http\Controllers\Controller;
use App\Models\Billing\Invoice;
use App\Models\Billing\InvoiceItem;
use App\Models\Billing\Promotion;
use App\Models\Patient\Patient;
use App\Models\Visit\Visit;
use App\Modules\Audit\Services\AuditLogService;
use App\Modules\Billing\Actions\AddInvoiceItemAction;
use App\Modules\Billing\Actions\CancelInvoiceAction;
use App\Modules\Billing\Actions\CreateInvoiceFromVisitAction;
use App\Modules\Billing\Actions\DeleteInvoiceItemAction;
use App\Modules\Billing\Actions\FinalizeInvoiceAction;
use App\Modules\Billing\Actions\UpdateInvoiceAction;
use App\Modules\Billing\DTOs\CreateInvoiceItemData;
use App\Modules\Billing\DTOs\UpdateInvoiceData;
use App\Modules\Billing\Requests\AddInvoiceItemRequest;
use App\Modules\Billing\Requests\CancelInvoiceRequest;
use App\Modules\Billing\Requests\StoreInvoiceRequest;
use App\Modules\Billing\Requests\UpdateInvoiceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    use AppliesSpecialtyScope;

    public function __construct(
        private CreateInvoiceFromVisitAction $createInvoiceAction,
        private UpdateInvoiceAction $updateInvoiceAction,
        private AddInvoiceItemAction $addItemAction,
        private DeleteInvoiceItemAction $deleteItemAction,
        private FinalizeInvoiceAction $finalizeAction,
        private CancelInvoiceAction $cancelAction,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function index(Request $request): View
    {
        $query = $this->scopeInvoices(Invoice::query()->with(['patient', 'visit', 'promotion', 'createdBy']))
            ->withCount(['items', 'payments']);

        if ($request->filled('status')) {
            $query->where('status', (string) $request->string('status'));
        }

        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->integer('patient_id'));
        }

        if ($request->filled('search')) {
            $search = (string) $request->string('search');
            $query->where(function ($builder) use ($search) {
                $builder->where('invoice_no', 'like', "%{$search}%")
                    ->orWhereHas('patient', function ($patientQuery) use ($search) {
                        $patientQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('full_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('visit', function ($visitQuery) use ($search) {
                        $visitQuery->where('visit_no', 'like', "%{$search}%");
                    });
            });
        }

        $invoices = $query->latest('issued_at')->latest()->paginate(15)->withQueryString();
        $statsQuery = $this->scopeInvoices(Invoice::query());

        return view('admin.billing.invoices', [
            'invoices' => $invoices,
            'statistics' => [
                'total' => (clone $statsQuery)->count(),
                'paid' => (clone $statsQuery)->where('status', InvoiceStatus::PAID->value)->count(),
                'partial' => (clone $statsQuery)->where('status', InvoiceStatus::PARTIALLY_PAID->value)->count(),
                'unpaid' => (clone $statsQuery)->unpaid()->count(),
                'cancelled' => (clone $statsQuery)->where('status', InvoiceStatus::CANCELLED->value)->count(),
                'totalAmount' => (float) (clone $statsQuery)->sum('total'),
                'paidAmount' => (float) (clone $statsQuery)->sum('paid_amount'),
                'dueAmount' => (float) (clone $statsQuery)->sum('remaining_amount'),
            ],
            'patients' => $this->scopePatients(Patient::query())->orderBy('first_name', 'asc')->get(['id', 'first_name', 'last_name', 'full_name']),
            'statuses' => InvoiceStatus::cases(),
        ]);
    }

    public function create(Request $request): View
    {
        $selectedPatientId = $request->integer('patient_id') ?: null;

        if ($selectedPatientId) {
            $patient = Patient::query()->findOrFail($selectedPatientId);
            $this->ensureCanAccessPatient($patient);
        }

        return view('admin.billing.invoice-form', [
            'invoice' => new Invoice([
                'patient_id' => $selectedPatientId,
                'visit_id' => $request->integer('visit_id') ?: null,
            ]),
            'patients' => $this->scopePatients(Patient::query())->orderBy('first_name', 'asc')->get(),
            'visits' => $selectedPatientId
                ? $this->scopeVisits(Visit::query()->with('patient')->where('patient_id', $selectedPatientId))->latest('visit_date')->get()
                : collect(),
            'promotions' => Promotion::activeNow()->orderBy('title_en')->get(),
            'discountTypes' => DiscountType::cases(),
            'statuses' => InvoiceStatus::cases(),
            'isEdit' => false,
        ]);
    }

    public function store(StoreInvoiceRequest $request): RedirectResponse
    {
        $patient = Patient::query()->findOrFail($request->integer('patient_id'));
        $this->ensureCanAccessPatient($patient);

        if ($request->integer('visit_id')) {
            $visit = Visit::query()->findOrFail($request->integer('visit_id'));
            $this->ensureCanAccessVisit($visit);
        }

        $invoice = ($this->createInvoiceAction)(
            patientId: $request->integer('patient_id'),
            createdBy: (int) auth()->id(),
            visitId: $request->integer('visit_id') ?: null,
            promotionId: $request->integer('promotion_id') ?: null,
            notes: $request->input('notes'),
        );

        if ($request->filled('discount_type') || $request->filled('discount_value')) {
            $invoice = ($this->updateInvoiceAction)(
                $invoice,
                UpdateInvoiceData::fromArray($request->only(['promotion_id', 'notes', 'discount_type', 'discount_value']))
            );
        }

        $this->auditLogService->log('billing', 'create', $invoice, null, $invoice->fresh()?->toArray() ?? $invoice->toArray());

        return redirect()
            ->route('admin.billing.invoices.show', $invoice)
            ->with('success', __('admin.billing.invoice_created'));
    }

    public function show(Invoice $invoice): View
    {
        $this->ensureCanAccessInvoice($invoice);

        $invoice->load(['patient', 'visit', 'items.service', 'payments.receivedBy', 'createdBy', 'promotion']);

        return view('admin.billing.invoice-show', [
            'invoice' => $invoice,
            'promotions' => Promotion::activeNow()->orderBy('title_en')->get(),
            'discountTypes' => DiscountType::cases(),
            'paymentMethods' => \App\Enums\PaymentMethod::cases(),
        ]);
    }

    public function edit(Invoice $invoice): View
    {
        $this->ensureCanAccessInvoice($invoice);

        $invoice->load(['patient', 'visit', 'promotion', 'items', 'payments']);

        $selectedPatientId = request()->integer('patient_id') ?: $invoice->patient_id;

        return view('admin.billing.invoice-form', [
            'invoice' => $invoice,
            'patients' => $this->scopePatients(Patient::query())->orderBy('first_name', 'asc')->get(),
            'visits' => $this->scopeVisits(Visit::query()->with('patient')->where('patient_id', $selectedPatientId))->latest('visit_date')->get(),
            'promotions' => Promotion::activeNow()->orderBy('title_en')->get(),
            'discountTypes' => DiscountType::cases(),
            'statuses' => InvoiceStatus::cases(),
            'isEdit' => true,
        ]);
    }

    public function visitsByPatient(Patient $patient): JsonResponse
    {
        $this->ensureCanAccessPatient($patient);

        $visits = $this->scopeVisits(Visit::query())
            ->where('patient_id', $patient->id)

            ->get(['id', 'visit_no', 'visit_date', 'status']);

        return response()->json([
            'visits' => $visits->map(fn (Visit $visit) => [
                'id' => $visit->id,
                'visit_no' => $visit->visit_no,
                'visit_date' => optional($visit->visit_date)->format('Y-m-d'),
                'status' => $visit->status?->value ?? (string) $visit->status,
            ])->values(),
        ]);
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice): RedirectResponse
    {
        $this->ensureCanAccessInvoice($invoice);

        if (! $invoice->can_be_edited) {
            return back()->withErrors(['invoice' => __('admin.billing.error_invoice_not_editable')]);
        }

        $before = $invoice->toArray();
        ($this->updateInvoiceAction)($invoice, UpdateInvoiceData::fromArray($request->validated()));
        $invoice = $invoice->fresh();

        $this->auditLogService->log('billing', 'update', $invoice, $before, $invoice?->toArray());

        return redirect()
            ->route('admin.billing.invoices.show', $invoice)
            ->with('success', __('admin.billing.invoice_updated'));
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $this->ensureCanAccessInvoice($invoice);

        if ($invoice->has_payments) {
            return back()->withErrors(['invoice' => __('admin.billing.error_paid_invoice')]);
        }

        $before = $invoice->loadMissing('items')->toArray();
        $invoice->items()->delete();
        $invoice->delete();

        $this->auditLogService->log('billing', 'delete', Invoice::class, $before, null);

        return redirect()
            ->route('admin.billing.invoices.index')
            ->with('success', __('admin.billing.invoice_deleted'));
    }

    public function addItem(AddInvoiceItemRequest $request, Invoice $invoice): RedirectResponse
    {
        $this->ensureCanAccessInvoice($invoice);

        if (! $invoice->can_be_edited) {
            return back()->withErrors(['invoice' => __('admin.billing.error_invoice_not_editable')]);
        }

        ($this->addItemAction)(
            $invoice,
            CreateInvoiceItemData::fromArray([
                ...$request->validated(),
                'invoice_id' => $invoice->id,
            ])
        );

        $this->auditLogService->log('billing', 'billing_action', $invoice->fresh() ?? $invoice, null, [
            'operation' => 'invoice_item_added',
            'invoice_id' => $invoice->id,
            'payload' => $request->validated(),
        ]);

        return redirect()
            ->route('admin.billing.invoices.show', $invoice)
            ->with('success', __('admin.billing.item_added'));
    }

    public function deleteItem(Invoice $invoice, InvoiceItem $item): RedirectResponse
    {
        $this->ensureCanAccessInvoice($invoice);

        abort_unless($item->invoice_id === $invoice->id, 404);

        if (! $invoice->can_be_edited) {
            return back()->withErrors(['invoice' => __('admin.billing.error_invoice_not_editable')]);
        }

        $before = $item->toArray();
        ($this->deleteItemAction)($item);

        $this->auditLogService->log('billing', 'billing_action', $invoice, [
            'operation' => 'invoice_item_deleted',
            'item' => $before,
        ], null);

        return redirect()
            ->route('admin.billing.invoices.show', $invoice)
            ->with('success', __('admin.billing.item_removed'));
    }

    public function finalize(Invoice $invoice): RedirectResponse
    {
        $this->ensureCanAccessInvoice($invoice);

        $before = $invoice->toArray();
        ($this->finalizeAction)($invoice);
        $invoice = $invoice->fresh();

        $this->auditLogService->log('billing', 'status_change', $invoice, $before, $invoice?->toArray());

        return redirect()
            ->route('admin.billing.invoices.show', $invoice)
            ->with('success', __('admin.billing.invoice_finalized'));
    }

    public function cancel(CancelInvoiceRequest $request, Invoice $invoice): RedirectResponse
    {
        $this->ensureCanAccessInvoice($invoice);

        $before = $invoice->toArray();
        ($this->cancelAction)($invoice, $request->input('reason'));
        $invoice = $invoice->fresh();

        $this->auditLogService->log('billing', 'status_change', $invoice, $before, array_merge($invoice?->toArray() ?? [], [
            'reason' => $request->input('reason'),
        ]));

        return redirect()
            ->route('admin.billing.invoices.show', $invoice)
            ->with('success', __('admin.billing.invoice_cancelled'));
    }

    public function print(Invoice $invoice): View
    {
        $this->ensureCanAccessInvoice($invoice);

        $invoice->load(['patient', 'visit', 'items', 'payments.receivedBy', 'promotion', 'createdBy']);

        return view('admin.billing.invoice-print', [
            'invoice' => $invoice,
        ]);
    }
}
