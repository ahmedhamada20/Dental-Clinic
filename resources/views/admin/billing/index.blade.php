@extends('admin.layouts.app')

@section('title', __('admin.billing.billing_management'))

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">

            </li>

        </ol>
    </nav>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Billing Overview Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-left border-primary">
                <div class="card-body">
                    <div class="text-primary mb-2">
                        <i class="bi bi-receipt fs-3"></i>
                    </div>
                    <h6 class="text-muted mb-1">{{ __('admin.billing.total_invoices') }}</h6>
                    <h3 class="mb-0">{{ $totalInvoices ?? 0 }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left border-success">
                <div class="card-body">
                    <div class="text-success mb-2">
                        <i class="bi bi-check-circle fs-3"></i>
                    </div>
                    <h6 class="text-muted mb-1">{{ __('admin.billing.paid') }}</h6>
                    <h3 class="mb-0">{{ $paidInvoices ?? 0 }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left border-warning">
                <div class="card-body">
                    <div class="text-warning mb-2">
                        <i class="bi bi-clock-history fs-3"></i>
                    </div>
                    <h6 class="text-muted mb-1">{{ __('admin.billing.unpaid') }}</h6>
                    <h3 class="mb-0">{{ $unpaidInvoices ?? 0 }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left border-danger">
                <div class="card-body">
                    <div class="text-danger mb-2">
                        <i class="bi bi-exclamation-circle fs-3"></i>
                    </div>
                    <h6 class="text-muted mb-1">{{ __('admin.billing.monthly_revenue') }}</h6>
                    <h3 class="mb-0">${{ number_format($monthlyRevenue ?? 0, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="mb-0"><i class="bi bi-lightning-charge-fill text-warning me-2"></i>{{ __('admin.billing.quick_actions') }}</h5>
                        </div>
                        <div class="col-md-4 text-end d-flex gap-2 justify-content-end flex-wrap">
                            <a href="{{ route('admin.billing.invoices.create') }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-circle"></i> {{ __('admin.billing.new_invoice') }}
                            </a>
                            <a href="{{ route('admin.billing.invoices.index') }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-receipt"></i> {{ __('admin.billing.all_invoices') }}
                            </a>
                            <a href="{{ route('admin.billing.payments.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-credit-card"></i> {{ __('admin.billing.payments') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs Section -->
    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="invoices-tab" data-bs-toggle="tab" data-bs-target="#invoices" type="button" role="tab" aria-controls="invoices" aria-selected="true">
                        <i class="bi bi-receipt"></i> {{ __('admin.billing.invoices') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="payments-tab" data-bs-toggle="tab" data-bs-target="#payments" type="button" role="tab" aria-controls="payments" aria-selected="false">
                        <i class="bi bi-credit-card"></i> {{ __('admin.billing.payments') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="summary-tab" data-bs-toggle="tab" data-bs-target="#summary" type="button" role="tab" aria-controls="summary" aria-selected="false">
                        <i class="bi bi-graph-up"></i> {{ __('admin.billing.summary') }}
                    </button>
                </li>
            </ul>
        </div>

        <div class="tab-content card-body">
            <!-- Invoices Tab -->
            <div class="tab-pane fade show active" id="invoices" role="tabpanel" aria-labelledby="invoices-tab">
                <div class="mb-3">
                    <form method="GET" action="{{ route('admin.billing.invoices.index') }}" class="row g-2">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="{{ __('admin.billing.search_invoices') }}" value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-select form-select-sm">
                                <option value="">{{ __('admin.billing.all_status') }}</option>
                                <option value="unpaid" @selected(request('status') === 'unpaid')>{{ __('admin.billing.unpaid') }}</option>
                                <option value="partially_paid" @selected(request('status') === 'partially_paid')>{{ __('admin.billing.partial') }}</option>
                                <option value="paid" @selected(request('status') === 'paid')>{{ __('admin.billing.paid') }}</option>
                                <option value="cancelled" @selected(request('status') === 'cancelled')>{{ __('admin.billing.cancelled') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="date" class="form-control form-control-sm" value="{{ request('date') }}">
                        </div>
                        <div class="col-md-2 d-flex gap-1">
                            <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                                <i class="bi bi-search"></i> {{ __('common.filter') }}
                            </button>
                            <a href="{{ route('admin.billing.invoices.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-arrow-clockwise"></i>
                            </a>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="invoicesTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 80px;">{{ __('admin.billing.invoice_no') }}</th>
                                <th>{{ __('admin.billing.patient') }}</th>
                                <th style="width: 100px;">{{ __('admin.amount') }}</th>
                                <th style="width: 100px;">{{ __('admin.billing.due_date') }}</th>
                                <th style="width: 100px;">{{ __('admin.status') }}</th>
                                <th style="width: 150px;">{{ __('admin.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentInvoices ?? [] as $invoice)
                                <tr>
                                    <td><strong>#{{ $invoice->invoice_no ?? $invoice->id }}</strong></td>
                                    <td>{{ $invoice->patient?->display_name ?? $invoice->patient?->full_name ?? __('common.not_available') }}</td>
                                    <td>${{ number_format($invoice->total ?? 0, 2) }}</td>
                                    <td>{{ $invoice->issued_at?->format('M d, Y') ?? __('common.not_available') }}</td>
                                    <td>
                                        @php($invoiceStatus = $invoice->status?->value ?? $invoice->status)
                                        <span class="badge bg-{{ match($invoiceStatus ?? 'unpaid') {
                                            'paid' => 'success',
                                            'partially_paid' => 'warning',
                                            'cancelled' => 'danger',
                                            default => 'secondary'
                                        } }}">
                                            {{ $invoice->status?->label() ?? ucfirst(str_replace('_', ' ', (string) $invoiceStatus)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('admin.billing.invoices.show', $invoice) }}" class="btn btn-outline-primary" title="{{ __('common.view') }}">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.billing.invoices.edit', $invoice) }}" class="btn btn-outline-warning" title="{{ __('common.edit') }}">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger" title="{{ __('common.delete') }}" onclick="confirmDelete('{{ route('admin.billing.invoices.destroy', $invoice) }}')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="card-footer text-muted small">
                    {{ __('admin.billing.showing_latest') }}
                </div>
            </div>

            <!-- Payments Tab -->
            <div class="tab-pane fade" id="payments" role="tabpanel" aria-labelledby="payments-tab">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('admin.billing.payment_no') }}</th>
                                <th>{{ __('admin.billing.invoice_no') }}</th>
                                <th>{{ __('admin.billing.patient') }}</th>
                                <th>{{ __('admin.amount') }}</th>
                                <th>{{ __('admin.billing.payment_method_short') }}</th>
                                <th>{{ __('admin.date') }}</th>
                                <th>{{ __('admin.status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPayments ?? [] as $payment)
                                <tr>
                                    <td>{{ $payment->payment_no ?? $payment->id }}</td>
                                    <td>#{{ $payment->invoice?->invoice_no ?? __('common.not_available') }}</td>
                                    <td>{{ $payment->patient?->display_name ?? $payment->invoice?->patient?->display_name ?? __('common.not_available') }}</td>
                                    <td>${{ number_format($payment->amount ?? 0, 2) }}</td>
                                    <td>{{ $payment->payment_method?->value ? ucfirst(str_replace('_', ' ', $payment->payment_method->value)) : __('common.not_available') }}</td>
                                    <td>{{ $payment->payment_date?->format('M d, Y') ?? __('common.not_available') }}</td>
                                    <td>
                                        <span class="badge bg-success">{{ __('admin.billing.completed_status') }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        {{ __('admin.billing.no_payments_found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Summary Tab -->
            <div class="tab-pane fade" id="summary" role="tabpanel" aria-labelledby="summary-tab">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="mb-3">{{ __('admin.billing.revenue_by_status') }}</h6>
                        <canvas id="statusChart"></canvas>
                    </div>
                    <div class="col-md-6">
                        <h6 class="mb-3">{{ __('admin.billing.monthly_revenue') }}</h6>
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">{{ __('admin.billing.delete_confirmation') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('admin.billing.delete_invoice_confirm') }}</p>
                <p class="text-muted"><small>{{ __('admin.billing.action_undone') }}</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                <form method="POST" id="deleteForm" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">{{ __('common.delete') }}</button>
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

// Initialize DataTables
document.addEventListener('DOMContentLoaded', function() {
    const cancelledInvoices = Math.max(({{ (int) ($totalInvoices ?? 0) }} - {{ (int) ($paidInvoices ?? 0) }} - {{ (int) ($unpaidInvoices ?? 0) }}), 0);

    const table = document.getElementById('invoicesTable');
    if (table && !$.fn.DataTable.isDataTable(table)) {
        $(table).DataTable({
            pageLength: 15,
            responsive: true,
            order: [[0, 'desc']],
            language: {
                emptyTable: '{{ __('admin.billing.no_invoices_found') }}'
            },
            columnDefs: [
                { orderable: false, targets: -1 }
            ]
        });
    }

    // Chart.js setup (if Chart.js is available)
    const statusCtx = document.getElementById('statusChart');
    const monthlyCtx = document.getElementById('monthlyChart');

    if (statusCtx && typeof Chart !== 'undefined') {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['{{ __('admin.billing.paid') }}', '{{ __('admin.billing.unpaid') }}', '{{ __('admin.billing.cancelled') }}'],
                datasets: [{
                    data: [{{ (int) ($paidInvoices ?? 0) }}, {{ (int) ($unpaidInvoices ?? 0) }}, cancelledInvoices],
                    backgroundColor: ['#198754', '#ffc107', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }

    if (monthlyCtx && typeof Chart !== 'undefined') {
        new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: ['{{ __('admin.billing.current_month') }}'],
                datasets: [{
                    label: '{{ __('admin.billing.revenue_label') }}',
                    data: [{{ (float) ($monthlyRevenue ?? 0) }}],
                    backgroundColor: '#0d6efd'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
});
</script>
@endpush
