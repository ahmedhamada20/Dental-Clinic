@extends('admin.layouts.app')

@section('title', __('admin.billing.payments'))

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-2">{{ __('admin.billing.payments') }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.billing.invoices.index') }}">{{ __('admin.billing.title') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('admin.billing.payment_list') }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-2">{{ __('admin.billing.total_payments') }}</h6>
                    <h3 class="mb-0">{{ $statistics['total'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-2">{{ __('admin.billing.total_amount') }}</h6>
                    <h3 class="mb-0">{{ number_format($statistics['totalAmount'] ?? 0, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-2">{{ __('admin.billing.today') }}</h6>
                    <h3 class="mb-0">{{ number_format($statistics['todayAmount'] ?? 0, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-2">{{ __('admin.billing.this_month') }}</h6>
                    <h3 class="mb-0">{{ number_format($statistics['monthAmount'] ?? 0, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card">
        <!-- Filter Section -->
        <div class="card-header">
            <form method="GET" class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="{{ __('admin.billing.payment_search_placeholder') }}" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="payment_method" class="form-select form-select-sm">
                        <option value="">{{ __('admin.billing.all_methods') }}</option>
                        @foreach ($paymentMethods as $method)
                            <option value="{{ $method->value }}" @selected(request('payment_method') === $method->value)>
                                {{ $method->label() ?? $method->value }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="patient_id" class="form-select form-select-sm">
                        <option value="">{{ __('admin.billing.all_patients') }}</option>
                        @foreach ($patients as $patient)
                            <option value="{{ $patient->id }}" @selected(request('patient_id') == $patient->id)>
                                {{ $patient->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="d-grid gap-2" style="grid-template-columns: 1fr 1fr;">
                        <button type="submit" class="btn btn-sm btn-primary">{{ __('admin.filter') }}</button>
                        <a href="{{ route('admin.billing.payments.index') }}" class="btn btn-sm btn-secondary">{{ __('admin.reset') }}</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('admin.billing.payment_no') }}</th>
                        <th>{{ __('admin.billing.patient') }}</th>
                        <th>{{ __('admin.billing.invoice_no') }}</th>
                        <th>{{ __('admin.billing.payment_date') }}</th>
                        <th>{{ __('admin.billing.payment_method') }}</th>
                        <th>{{ __('admin.billing.reference_no') }}</th>
                        <th class="text-end">{{ __('admin.billing.amount') }}</th>
                        <th class="text-end">{{ __('admin.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr>
                            <td>
                                <strong>{{ $payment->payment_no ?? 'PAY-' . $payment->id }}</strong>
                            </td>
                            <td>
                                @if ($payment->patient)
                                    <a href="{{ route('admin.patients.show', $payment->patient) }}">
                                        {{ $payment->patient->full_name }}
                                    </a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if ($payment->invoice)
                                    <a href="{{ route('admin.billing.invoices.show', $payment->invoice) }}">
                                        {{ $payment->invoice->invoice_no ?? 'INV-' . $payment->invoice->id }}
                                    </a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>{{ optional($payment->payment_date)->format('Y-m-d') }}</td>
                            <td>
                                <span class="badge text-bg-info">
                                    {{ $payment->payment_method?->label() ?? str_replace('_', ' ', ucfirst($payment->payment_method)) }}
                                </span>
                            </td>
                            <td>{{ $payment->reference_no ?? '-' }}</td>
                            <td class="text-end">{{ number_format($payment->amount, 2) }}</td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm" role="group">
                                    @can('payments.view')
                                        <a href="{{ route('admin.billing.payments.show', $payment) }}"
                                           class="btn btn-outline-primary" title="{{ __('admin.billing.view_payment') }}">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    @endcan
                                    @can('payments.delete')
                                        <button class="btn btn-outline-danger" title="{{ __('admin.billing.delete_payment') }}"
                                                onclick="confirm('{{ __('admin.billing.confirm_delete') }}') && document.getElementById('delete-form-{{ $payment->id }}').submit()">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <form id="delete-form-{{ $payment->id }}"
                                              action="{{ route('admin.billing.payments.destroy', $payment) }}"
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
                                <i class="bi bi-inbox"></i> {{ __('admin.billing.no_payments_found') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($payments->hasPages())
            <div class="card-footer">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

