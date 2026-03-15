@extends('admin.layouts.app')

@section('title', __('admin.billing.invoices'))

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-2">{{ __('admin.billing.invoices') }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">{{ __('admin.billing.title') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('admin.billing.invoice_list') }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            @can('invoices.create')
                <a href="{{ route('admin.billing.invoices.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle"></i> {{ __('admin.billing.new_invoice') }}
                </a>
            @endcan
            @can('invoices.view')
                <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#filterModal">
                    <i class="bi bi-funnel"></i> {{ __('admin.filter') }}
                </button>
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

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-2">{{ __('admin.billing.total_invoices') }}</h6>
                    <h3 class="mb-0">{{ $statistics['total'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-2">{{ __('admin.billing.total_revenue') }}</h6>
                    <h3 class="mb-0">{{ number_format($statistics['totalAmount'] ?? 0, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-2">{{ __('admin.billing.total_paid') }}</h6>
                    <h3 class="mb-0">{{ number_format($statistics['paidAmount'] ?? 0, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-2">{{ __('admin.billing.total_pending') }}</h6>
                    <h3 class="mb-0">{{ number_format($statistics['dueAmount'] ?? 0, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('admin.billing.invoice_no') }}</th>
                        <th>{{ __('admin.billing.patient') }}</th>
                        <th>{{ __('admin.billing.issued_at') }}</th>
                        <th>{{ __('admin.billing.total_amount') }}</th>
                        <th>{{ __('admin.billing.paid_amount') }}</th>
                        <th>{{ __('admin.billing.remaining_amount') }}</th>
                        <th>{{ __('admin.status') }}</th>
                        <th class="text-end">{{ __('admin.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr>
                            <td>
                                <strong>{{ $invoice->invoice_no ?? 'INV-' . $invoice->id }}</strong>
                            </td>
                            <td>
                                @if ($invoice->patient)
                                    <a href="{{ route('admin.patients.show', $invoice->patient) }}">
                                        {{ $invoice->patient->full_name }}
                                    </a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>{{ optional($invoice->issued_at)->format('Y-m-d H:i') ?? '-' }}</td>
                            <td>{{ number_format($invoice->total, 2) }}</td>
                            <td>{{ number_format($invoice->paid_amount, 2) }}</td>
                            <td>{{ number_format($invoice->remaining_amount ?? ($invoice->total - $invoice->paid_amount), 2) }}</td>
                            <td>
                                @php
                                    $statusBadgeClass = match($invoice->status?->value ?? $invoice->status) {
                                        'paid' => 'success',
                                        'partially_paid' => 'warning',
                                        'unpaid' => 'danger',
                                        'cancelled' => 'secondary',
                                        default => 'info'
                                    };
                                @endphp
                                <span class="badge text-bg-{{ $statusBadgeClass }}">
                                    {{ $invoice->status?->label() ?? str((string) $invoice->status)->replace('_', ' ')->title() }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm" role="group">
                                    @can('invoices.view')
                                        <a href="{{ route('admin.billing.invoices.show', $invoice) }}"
                                           class="btn btn-outline-primary" title="{{ __('admin.billing.view_invoice') }}">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    @endcan
                                    @can('invoices.edit')
                                        <a href="{{ route('admin.billing.invoices.edit', $invoice) }}"
                                           class="btn btn-outline-warning" title="{{ __('admin.billing.edit_invoice') }}">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan
                                    @can('invoices.delete')
                                        <button class="btn btn-outline-danger" title="{{ __('admin.billing.delete_invoice') }}"
                                                onclick="confirm('{{ __('admin.billing.confirm_delete') }}') && document.getElementById('delete-form-{{ $invoice->id }}').submit()">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <form id="delete-form-{{ $invoice->id }}"
                                              action="{{ route('admin.billing.invoices.destroy', $invoice) }}"
                                              method="POST" style="display:none">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox"></i> {{ __('admin.billing.no_invoices_found') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($invoices->hasPages())
            <div class="card-footer">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('admin.filter') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="GET">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('admin.billing.status') }}</label>
                        <select name="status" class="form-select">
                            <option value="">{{ __('admin.billing.all_statuses') }}</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>
                                    {{ $status->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('admin.billing.patient') }}</label>
                        <select name="patient_id" class="form-select">
                            <option value="">{{ __('admin.billing.all_patients') }}</option>
                            @foreach ($patients as $patient)
                                <option value="{{ $patient->id }}" @selected(request('patient_id') == $patient->id)>
                                    {{ $patient->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('admin.search') }}</label>
                        <input type="text" name="search" class="form-control" placeholder="{{ __('admin.billing.invoice_search_placeholder') }}" value="{{ request('search') }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('admin.billing.invoices.index') }}" class="btn btn-secondary">{{ __('admin.reset') }}</a>
                    <button type="submit" class="btn btn-primary">{{ __('admin.filter') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

