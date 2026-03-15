@extends('admin.layouts.app')

@section('title', __('Invoice Details'))

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.dashboard.index') }}">{{ __('Dashboard') }}</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('admin.billing.index') }}">{{ __('Billing') }}</a>
            </li>
            <li class="breadcrumb-item active">{{ __('Invoice #') }}{{ $invoice->invoice_no }}</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <!-- Invoice Card -->
            <div class="card">
                <div class="card-header bg-white d-print-none">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0">
                                {{ __('Invoice') }} #{{ $invoice->invoice_no }}
                                <span class="badge bg-{{ match($invoice->status ?? 'draft') {
                                    'paid' => 'success',
                                    'sent' => 'info',
                                    'overdue' => 'danger',
                                    default => 'secondary'
                                } }} ms-2">
                                    {{ ucfirst($invoice->status ?? 'draft') }}
                                </span>
                            </h5>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="btn-group btn-group-sm" role="group">
                                @if($invoice->status === 'draft')
                                    <a href="{{ route('admin.billing.invoices.edit', $invoice) }}" class="btn btn-warning">
                                        <i class="bi bi-pencil"></i> {{ __('Edit') }}
                                    </a>
                                @endif
                                <a href="{{ route('admin.billing.invoices.print', $invoice) }}" class="btn btn-outline-primary" target="_blank">
                                    <i class="bi bi-printer"></i> {{ __('Print') }}
                                </a>
                                <button type="button" class="btn btn-outline-danger" onclick="confirmDelete('{{ route('admin.billing.invoices.destroy', $invoice) }}')">
                                    <i class="bi bi-trash"></i> {{ __('Delete') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Invoice Header -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">{{ __('Invoice Information') }}</h6>
                            <dl class="small mb-0">
                                <dt>{{ __('Invoice Number') }}</dt>
                                <dd>{{ $invoice->invoice_no }}</dd>

                                <dt>{{ __('Date') }}</dt>
                                <dd>{{ $invoice->invoice_date?->format('M d, Y') }}</dd>

                                <dt>{{ __('Due Date') }}</dt>
                                <dd>{{ $invoice->due_date?->format('M d, Y') }}</dd>

                                <dt>{{ __('Status') }}</dt>
                                <dd>
                                    <span class="badge bg-{{ match($invoice->status ?? 'draft') {
                                        'paid' => 'success',
                                        'sent' => 'info',
                                        'overdue' => 'danger',
                                        default => 'secondary'
                                    } }}">
                                        {{ ucfirst($invoice->status ?? 'draft') }}
                                    </span>
                                </dd>
                            </dl>
                        </div>

                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">{{ __('Patient Information') }}</h6>
                            <dl class="small mb-0">
                                <dt>{{ __('Name') }}</dt>
                                <dd>
                                    <a href="{{ route('admin.patients.show', $invoice->patient) }}">
                                        {{ $invoice->patient?->name }}
                                    </a>
                                </dd>

                                <dt>{{ __('Phone') }}</dt>
                                <dd>{{ $invoice->patient?->phone }}</dd>

                                <dt>{{ __('Email') }}</dt>
                                <dd>{{ $invoice->patient?->email }}</dd>

                                <dt>{{ __('Address') }}</dt>
                                <dd>{{ $invoice->patient?->address ?? __('N/A') }}</dd>
                            </dl>
                        </div>
                    </div>

                    <hr>

                    <!-- Invoice Items Table -->
                    <div class="mb-4">
                        <h6 class="text-muted mb-2">{{ __('Invoice Items') }}</h6>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('Description') }}</th>
                                        <th class="text-end" style="width: 80px;">{{ __('Quantity') }}</th>
                                        <th class="text-end" style="width: 100px;">{{ __('Unit Price') }}</th>
                                        <th class="text-end" style="width: 100px;">{{ __('Amount') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($invoice->items ?? [] as $item)
                                        <tr>
                                            <td>{{ $item->description }}</td>
                                            <td class="text-end">{{ $item->quantity }}</td>
                                            <td class="text-end">${{ number_format($item->unit_price ?? 0, 2) }}</td>
                                            <td class="text-end">${{ number_format(($item->quantity ?? 1) * ($item->unit_price ?? 0), 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-3">
                                                {{ __('No items') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Totals -->
                    <div class="row">
                        <div class="col-md-6"></div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <td>{{ __('Subtotal') }}</td>
                                    <td class="text-end">${{ number_format($invoice->subtotal ?? 0, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>{{ __('Tax') }} ({{ $invoice->tax_rate ?? 0 }}%)</td>
                                    <td class="text-end">${{ number_format($invoice->tax_amount ?? 0, 2) }}</td>
                                </tr>
                                <tr class="table-light fw-bold">
                                    <td>{{ __('Total') }}</td>
                                    <td class="text-end">${{ number_format($invoice->total ?? 0, 2) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($invoice->description)
                        <hr>
                        <h6 class="text-muted mb-2">{{ __('Notes') }}</h6>
                        <p class="small">{{ $invoice->description }}</p>
                    @endif
                </div>
            </div>

            <!-- Payments Section -->
            @if($invoice->payments && $invoice->payments->count() > 0)
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-credit-card me-2"></i>{{ __('Payments Received') }}</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Method') }}</th>
                                    <th>{{ __('Reference') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->payments as $payment)
                                    <tr>
                                        <td>{{ $payment->created_at?->format('M d, Y') }}</td>
                                        <td>${{ number_format($payment->amount, 2) }}</td>
                                        <td>{{ ucfirst($payment->method ?? 'cash') }}</td>
                                        <td>{{ $payment->reference ?? __('N/A') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-md-4 d-print-none">
            <!-- Status Card -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>{{ __('Invoice Status') }}</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>{{ __('Current Status') }}</strong><br>
                        <span class="badge bg-{{ match($invoice->status ?? 'draft') {
                            'paid' => 'success',
                            'sent' => 'info',
                            'overdue' => 'danger',
                            default => 'secondary'
                        } }} class="mt-2">
                            {{ ucfirst($invoice->status ?? 'draft') }}
                        </span>
                    </div>

                    <hr class="my-2">

                    <div class="mb-3">
                        <strong>{{ __('Amount Due') }}</strong><br>
                        <h5 class="text-primary mt-2">
                            ${{ number_format(($invoice->total ?? 0) - ($invoice->payments?->sum('amount') ?? 0), 2) }}
                        </h5>
                    </div>

                    <div>
                        <strong>{{ __('Payment Status') }}</strong><br>
                        @php
                            $paid = $invoice->payments?->sum('amount') ?? 0;
                            $total = $invoice->total ?? 0;
                            $percentage = $total > 0 ? ($paid / $total) * 100 : 0;
                        @endphp
                        <div class="progress mt-2">
                            <div class="progress-bar bg-success" style="width: {{ $percentage }}%"></div>
                        </div>
                        <small class="text-muted">{{ number_format($percentage, 1) }}% {{ __('paid') }}</small>
                    </div>
                </div>
            </div>

            <!-- Actions Card -->
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-sliders me-2"></i>{{ __('Actions') }}</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($invoice->status === 'draft')
                            <a href="{{ route('admin.billing.invoices.edit', $invoice) }}" class="btn btn-warning btn-sm">
                                <i class="bi bi-pencil"></i> {{ __('Edit Invoice') }}
                            </a>
                            <form method="POST" action="{{ route('admin.billing.invoices.finalize', $invoice) }}">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm w-100" onclick="return confirm('{{ __('Finalize this invoice?') }}')">
                                    <i class="bi bi-check-circle"></i> {{ __('Finalize') }}
                                </button>
                            </form>
                        @endif

                        @if($invoice->status !== 'paid')
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
                                <i class="bi bi-plus-circle"></i> {{ __('Add Payment') }}
                            </button>
                        @endif

                        <a href="{{ route('admin.billing.invoices.print', $invoice) }}" class="btn btn-outline-primary btn-sm" target="_blank">
                            <i class="bi bi-printer"></i> {{ __('Print') }}
                        </a>

                        <a href="{{ route('admin.billing.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> {{ __('Back to Billing') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Payment Modal -->
<div class="modal fade" id="addPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Add Payment') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.billing.payments.store', $invoice) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="amount" class="form-label">{{ __('Amount') }} <span class="text-danger">*</span></label>
                        <input type="number" name="amount" id="amount" class="form-control" value="{{ number_format($invoice->total - ($invoice->payments?->sum('amount') ?? 0), 2) }}" step="0.01" required>
                        <small class="text-muted">{{ __('Amount due:') }} ${{ number_format($invoice->total - ($invoice->payments?->sum('amount') ?? 0), 2) }}</small>
                    </div>

                    <div class="mb-3">
                        <label for="method" class="form-label">{{ __('Payment Method') }} <span class="text-danger">*</span></label>
                        <select name="method" id="method" class="form-select" required>
                            <option value="cash">{{ __('Cash') }}</option>
                            <option value="credit_card">{{ __('Credit Card') }}</option>
                            <option value="debit_card">{{ __('Debit Card') }}</option>
                            <option value="check">{{ __('Check') }}</option>
                            <option value="bank_transfer">{{ __('Bank Transfer') }}</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="reference" class="form-label">{{ __('Reference / Note') }}</label>
                        <input type="text" name="reference" id="reference" class="form-control" placeholder="{{ __('Transaction ID, check number, etc.') }}">
                    </div>

                    <div class="mb-3">
                        <label for="payment_date" class="form-label">{{ __('Payment Date') }} <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" id="payment_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Add Payment') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">{{ __('Delete Invoice') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('Are you sure you want to delete this invoice?') }}</p>
                <p class="text-muted"><small>{{ __('This action cannot be undone.') }}</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <form method="POST" id="deleteForm" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">{{ __('Delete') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete(url) {
    document.getElementById('deleteForm').action = url;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush

