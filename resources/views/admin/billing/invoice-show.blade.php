@extends('admin.layouts.app')

@section('title', __('admin.billing.invoice_no') . ' - ' . ($invoice->invoice_no ?? 'INV-' . $invoice->id))

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="h4 mb-2">{{ __('admin.billing.invoice_no') }}: {{ $invoice->invoice_no ?? 'INV-' . $invoice->id }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.billing.invoices.index') }}">{{ __('admin.billing.invoices') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('admin.billing.details') }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            @can('invoices.view')
                <a href="{{ route('admin.billing.invoices.print', $invoice) }}" class="btn btn-outline-secondary btn-sm" target="_blank">
                    <i class="bi bi-printer"></i> {{ __('admin.billing.print_invoice') }}
                </a>
            @endcan
            @can('invoices.edit')
                <a href="{{ route('admin.billing.invoices.edit', $invoice) }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-pencil"></i> {{ __('admin.edit') }}
                </a>
            @endcan
            @can('invoices.delete')
                <button class="btn btn-outline-danger btn-sm" onclick="confirm('{{ __('admin.billing.confirm_delete') }}') && document.getElementById('delete-form').submit()">
                    <i class="bi bi-trash"></i> {{ __('admin.delete') }}
                </button>
                <form id="delete-form" action="{{ route('admin.billing.invoices.destroy', $invoice) }}" method="POST" style="display:none">
                    @csrf
                    @method('DELETE')
                </form>
            @endcan
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Invoice Details -->
        <div class="col-lg-8">
            <!-- Invoice Header Card -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">{{ __('admin.billing.patient') }}</h6>
                            @if ($invoice->patient)
                                <h5>
                                    <a href="{{ route('admin.patients.show', $invoice->patient) }}">
                                        {{ $invoice->patient->full_name }}
                                    </a>
                                </h5>
                                <small class="text-muted">ID: {{ $invoice->patient->id }}</small>
                            @else
                                <h5>N/A</h5>
                            @endif
                        </div>
                        <div class="col-md-6 text-end">
                            <h6 class="text-muted mb-1">{{ __('admin.billing.status') }}</h6>
                            @php
                                $statusBadgeClass = match($invoice->status?->value ?? $invoice->status) {
                                    'paid' => 'success',
                                    'partially_paid' => 'warning',
                                    'unpaid' => 'danger',
                                    'cancelled' => 'secondary',
                                    default => 'info'
                                };
                            @endphp
                            <span class="badge text-bg-{{ $statusBadgeClass }} fs-6">
                                {{ $invoice->status?->label() ?? str_replace('_', ' ', ucfirst($invoice->status)) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice Information -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <h6 class="text-muted">{{ __('admin.billing.invoice_no') }}</h6>
                            <h5>{{ $invoice->invoice_no ?? 'INV-' . $invoice->id }}</h5>
                        </div>
                        <div class="col-md-3">
                            <h6 class="text-muted">{{ __('admin.billing.issued_at') }}</h6>
                            <h5>{{ optional($invoice->issued_at)->format('Y-m-d H:i') ?? 'N/A' }}</h5>
                        </div>
                        <div class="col-md-3">
                            <h6 class="text-muted">{{ __('admin.billing.due_date') }}</h6>
                            <h5>{{ optional($invoice->due_date)->format('Y-m-d') ?? 'N/A' }}</h5>
                        </div>
                        <div class="col-md-3">
                            <h6 class="text-muted">{{ __('admin.billing.visit') }}</h6>
                            <h5>
                                @if ($invoice->visit)
                                    <a href="{{ route('admin.visits.show', $invoice->visit) }}">
                                        {{ $invoice->visit->visit_no }}
                                    </a>
                                @else
                                    N/A
                                @endif
                            </h5>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Line Items -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">{{ __('admin.billing.items') }}</h6>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('admin.billing.service') }}</th>
                                <th class="text-center">{{ __('admin.billing.quantity') }}</th>
                                <th class="text-end">{{ __('admin.billing.unit_price') }}</th>
                                <th class="text-end">{{ __('admin.billing.total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($invoice->items as $item)
                                <tr>
                                    <td>{{ $item->service?->name_en ?? $item->description }}</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-end">{{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">
                                        {{ __('admin.billing.no_line_items') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Payments Section -->
            @if (($invoice->status?->value ?? $invoice->status) !== 'cancelled')
                <div class="card mb-3">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">{{ __('admin.billing.payments') }}</h6>
                            @can('payments.create')
                                @if (!in_array($invoice->status?->value ?? $invoice->status, ['paid', 'cancelled'], true))
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#paymentModal">
                                        <i class="bi bi-plus"></i> {{ __('admin.billing.record_payment') }}
                                    </button>
                                @endif
                            @endcan
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('admin.billing.payment_no') }}</th>
                                    <th>{{ __('admin.billing.payment_date') }}</th>
                                    <th>{{ __('admin.billing.payment_method') }}</th>
                                    <th>{{ __('admin.billing.reference_no') }}</th>
                                    <th class="text-end">{{ __('admin.billing.amount') }}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($invoice->payments as $payment)
                                    <tr>
                                        <td>{{ $payment->payment_no ?? 'PAY-' . $payment->id }}</td>
                                        <td>{{ optional($payment->payment_date)->format('Y-m-d') }}</td>
                                        <td>{{ $payment->payment_method?->label() ?? str_replace('_', ' ', ucfirst($payment->payment_method)) }}</td>
                                        <td>{{ $payment->reference_no ?? '-' }}</td>
                                        <td class="text-end">{{ number_format($payment->amount, 2) }}</td>
                                        <td>
                                            @can('payments.delete')
                                                <button class="btn btn-sm btn-outline-danger"
                                                        onclick="confirm('{{ __('admin.billing.confirm_delete') }}') && document.getElementById('delete-payment-{{ $payment->id }}').submit()">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                                <form id="delete-payment-{{ $payment->id }}"
                                                      action="{{ route('admin.billing.payments.destroy', $payment) }}"
                                                      method="POST" style="display:none">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">
                                            {{ __('admin.billing.no_payments_recorded') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Invoice Notes -->
            @if ($invoice->notes)
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">{{ __('admin.billing.notes') }}</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $invoice->notes }}</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Summary Sidebar -->
        <div class="col-lg-4">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header">
                    <h6 class="mb-0">{{ __('admin.billing.summary') }}</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>{{ __('admin.billing.subtotal') }}:</span>
                        <strong>{{ number_format($invoice->subtotal ?? $invoice->total, 2) }}</strong>
                    </div>
                    @if ($invoice->discount_value)
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ __('admin.billing.discount') }}:</span>
                            <strong>-{{ number_format($invoice->discount_value, 2) }}</strong>
                        </div>
                    @endif
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <h6>{{ __('admin.billing.total') }}:</h6>
                        <h5>{{ number_format($invoice->total, 2) }}</h5>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>{{ __('admin.billing.paid_amount') }}:</span>
                        <strong class="text-success">{{ number_format($invoice->paid_amount, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>{{ __('admin.billing.remaining_amount') }}:</span>
                        <strong class="text-danger">{{ number_format($invoice->remaining_amount ?? ($invoice->total - $invoice->paid_amount), 2) }}</strong>
                    </div>
                </div>
                <div class="card-footer">
                    @can('invoices.view')
                        <div class="d-grid gap-2">
                            @if ($invoice->status?->value !== 'paid' && $invoice->status?->value !== 'cancelled')
                                @can('invoices.edit')
                                    <a href="{{ route('admin.billing.invoices.edit', $invoice) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i> {{ __('admin.billing.edit_invoice') }}
                                    </a>
                                @endcan
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#paymentModal">
                                    <i class="bi bi-cash-coin"></i> {{ __('admin.billing.record_payment') }}
                                </button>
                            @endif
                            @can('invoices.edit')
                                @if (!in_array($invoice->status?->value, ['paid', 'cancelled'], true))
                                    <form action="{{ route('admin.billing.invoices.finalize', $invoice) }}" method="POST" style="display:inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success w-100">
                                            <i class="bi bi-check-circle"></i> {{ __('admin.billing.finalize_invoice') }}
                                        </button>
                                    </form>
                                @endif
                            @endcan
                        </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.billing.payments.store', $invoice) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('admin.billing.record_payment') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('admin.billing.amount') }}<span class="text-danger">*</span></label>
                        <input type="number" name="payments[0][amount]" class="form-control" step="0.01" min="0"
                               max="{{ $invoice->remaining_amount ?? ($invoice->total - $invoice->paid_amount) }}" required>
                        <small class="text-muted">
                            {{ __('admin.billing.remaining_label') }} {{ number_format($invoice->remaining_amount ?? ($invoice->total - $invoice->paid_amount), 2) }}
                        </small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('admin.billing.payment_method') }}<span class="text-danger">*</span></label>
                        <select name="payments[0][payment_method]" class="form-select" required>
                            @foreach ($paymentMethods as $method)
                                <option value="{{ $method->value }}">{{ $method->label() ?? $method->value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('admin.billing.reference_no') }}</label>
                        <input type="text" name="payments[0][reference_no]" class="form-control" placeholder="e.g., Check #, Transaction ID">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('admin.billing.payment_date') }}</label>
                        <input type="date" name="payments[0][payment_date]" class="form-control" value="{{ now()->format('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('admin.billing.notes') }}</label>
                        <textarea name="payments[0][notes]" class="form-control" rows="2" placeholder="{{ __('admin.billing.additional_notes') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('admin.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('admin.billing.record_payment') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

