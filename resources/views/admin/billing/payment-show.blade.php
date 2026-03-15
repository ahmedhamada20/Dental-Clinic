@extends('admin.layouts.app')

@section('title', __('admin.billing.payment_no') . ' - ' . ($payment->payment_no ?? 'PAY-' . $payment->id))

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="h4 mb-2">{{ __('admin.billing.payment_no') }}: {{ $payment->payment_no ?? 'PAY-' . $payment->id }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.billing.payments.index') }}">{{ __('admin.billing.payments') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('admin.billing.details') }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            @can('payments.delete')
                <button class="btn btn-outline-danger btn-sm" onclick="confirm('{{ __('Are you sure?') }}') && document.getElementById('delete-form').submit()">
                    <i class="bi bi-trash"></i> {{ __('admin.delete') }}
                </button>
                <form id="delete-form" action="{{ route('admin.billing.payments.destroy', $payment) }}" method="POST" style="display:none">
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

    <div class="row">
        <!-- Payment Details -->
        <div class="col-lg-8">
            <!-- Payment Header Card -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">{{ __('admin.billing.status') }}</h6>
                            <h5 class="badge text-bg-success">{{ __('Recorded') }}</h5>
                        </div>
                        <div class="col-md-6 text-end">
                            <h6 class="text-muted mb-1">{{ __('admin.billing.payment_date') }}</h6>
                            <h5>{{ optional($payment->payment_date)->format('Y-m-d H:i') ?? 'N/A' }}</h5>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Patient Information -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">{{ __('Patient Information') }}</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">{{ __('admin.billing.patient') }}</h6>
                            @if ($payment->patient)
                                <p class="mb-0">
                                    <a href="{{ route('admin.patients.show', $payment->patient) }}">
                                        <strong>{{ $payment->patient->full_name }}</strong>
                                    </a><br>
                                    <small class="text-muted">ID: {{ $payment->patient->id }}</small>
                                </p>
                            @else
                                <p class="mb-0">N/A</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">{{ __('Contact') }}</h6>
                            <p class="mb-0">
                                @if ($payment->patient)
                                    {{ __('Phone') }}: {{ $payment->patient->phone ?? 'N/A' }}<br>
                                    {{ __('Email') }}: {{ $payment->patient->email ?? 'N/A' }}
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice Information -->
            @if ($payment->invoice)
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">{{ __('admin.billing.invoice_information') }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted">{{ __('admin.billing.invoice_no') }}</h6>
                                <p class="mb-0">
                                    <a href="{{ route('admin.billing.invoices.show', $payment->invoice) }}">
                                        <strong>{{ $payment->invoice->invoice_no ?? 'INV-' . $payment->invoice->id }}</strong>
                                    </a>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">{{ __('admin.billing.issued_at') }}</h6>
                                <p class="mb-0">{{ optional($payment->invoice->issued_at)->format('Y-m-d') }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">{{ __('admin.billing.invoice_total') }}</h6>
                                <p class="mb-0">{{ number_format($payment->invoice->total, 2) }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">{{ __('admin.billing.remaining_amount') }}</h6>
                                <p class="mb-0">{{ number_format($payment->invoice->remaining_amount ?? ($payment->invoice->total - $payment->invoice->paid_amount), 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Payment Details -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">{{ __('admin.billing.payment_details') }}</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">{{ __('admin.billing.payment_method') }}</h6>
                            <p class="mb-3">
                                <span class="badge text-bg-info">
                                    {{ $payment->payment_method?->label() ?? str_replace('_', ' ', ucfirst($payment->payment_method)) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">{{ __('admin.billing.reference_no') }}</h6>
                            <p class="mb-3"><strong>{{ $payment->reference_no ?? '-' }}</strong></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Notes -->
            @if ($payment->notes)
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">{{ __('admin.billing.notes') }}</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $payment->notes }}</p>
                    </div>
                </div>
            @endif

            <!-- Recorded By -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">{{ __('Recorded By') }}</h6>
                            <p class="mb-0">
                                @if ($payment->receivedBy)
                                    {{ $payment->receivedBy->name }}<br>
                                    <small class="text-muted">{{ optional($payment->created_at)->format('Y-m-d H:i:s') }}</small>
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Sidebar -->
        <div class="col-lg-4">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header">
                    <h6 class="mb-0">{{ __('admin.billing.payment_summary') }}</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <h6>{{ __('admin.billing.amount') }}:</h6>
                        <h4 class="text-success">{{ number_format($payment->amount, 2) }}</h4>
                    </div>
                    <hr>
                    <div class="row text-center">
                        <div class="col-6">
                            <h6 class="text-muted mb-2">{{ __('Payment ID') }}</h6>
                            <p class="mb-0"><small>{{ $payment->id }}</small></p>
                        </div>
                        <div class="col-6">
                            <h6 class="text-muted mb-2">{{ __('admin.billing.payment_date') }}</h6>
                            <p class="mb-0"><small>{{ optional($payment->payment_date)->format('M d, Y') }}</small></p>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    @if ($payment->invoice)
                        <div class="d-grid">
                            <a href="{{ route('admin.billing.invoices.show', $payment->invoice) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-receipt"></i> {{ __('View Invoice') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

