@extends('admin.layouts.app')

@section('title', isset($isEdit) && $isEdit ? __('admin.billing.edit_invoice') : __('admin.billing.new_invoice'))

@section('content')
@php
    $selectedPatientId = old('patient_id', $invoice->patient_id);
    $selectedVisitId = old('visit_id', $invoice->visit_id);
    $visitsByPatientUrlTemplate = route('admin.billing.patients.visits', ['patient' => '__PATIENT__']);
@endphp
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="mb-4">
        <h1 class="h4 mb-2">{{ isset($isEdit) && $isEdit ? __('admin.billing.edit_invoice') : __('admin.billing.new_invoice') }}</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.billing.invoices.index') }}">{{ __('admin.billing.invoices') }}</a></li>
                <li class="breadcrumb-item active">{{ isset($isEdit) && $isEdit ? __('admin.edit') : __('admin.create') }}</li>
            </ol>
        </nav>
    </div>

    <!-- Validation Errors -->
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h6 class="alert-heading mb-2"><i class="bi bi-exclamation-triangle"></i> {{ __('admin.validation_errors') }}</h6>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ isset($isEdit) && $isEdit ? route('admin.billing.invoices.update', $invoice) : route('admin.billing.invoices.store') }}"
          method="POST"
          enctype="multipart/form-data">
        @csrf
        @if (isset($isEdit) && $isEdit)
            @method('PUT')
        @endif

        <div class="row">
            <!-- Invoice Details Card -->
            <div class="col-lg-8">
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">{{ __('admin.billing.title') }} {{ __('admin.billing.details') }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Patient Selection -->
                            <div class="col-md-6">
                                <label class="form-label">{{ __('admin.billing.patient') }}<span class="text-danger">*</span></label>
                                <select id="invoice_patient_id" name="patient_id" class="form-select @error('patient_id') is-invalid @enderror" required>
                                    <option value="">{{ __('admin.billing.select_patient') }}</option>
                                    @foreach ($patients as $patient)
                                        <option value="{{ $patient->id }}" @selected((string) $selectedPatientId === (string) $patient->id)>
                                            {{ $patient->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('patient_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Visit Selection -->
                            <div class="col-md-6">
                                <label class="form-label">{{ __('admin.billing.visit') }}</label>
                                <select id="invoice_visit_id" name="visit_id" class="form-select @error('visit_id') is-invalid @enderror" @disabled(!$selectedPatientId)>
                                    <option value="">{{ __('admin.billing.select_visit') }}</option>
                                    @foreach ($visits as $visit)
                                        <option value="{{ $visit->id }}" @selected((string) $selectedVisitId === (string) $visit->id)>
                                            {{ $visit->visit_no }} - {{ $visit->patient->full_name ?? 'N/A' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('visit_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="col-md-6">
                                <label class="form-label">{{ __('admin.status') }}<span class="text-danger">*</span></label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status->value }}" @selected(old('status', $invoice->status?->value) === $status->value)>
                                            {{ $status->label() }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Issued Date -->
                            <div class="col-md-6">
                                <label class="form-label">{{ __('admin.billing.issued_at') }}</label>
                                <input type="datetime-local" name="issued_at" class="form-control @error('issued_at') is-invalid @enderror"
                                       value="{{ old('issued_at', optional($invoice->issued_at)->format('Y-m-d\TH:i')) }}">
                                @error('issued_at')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Promotion -->
                            <div class="col-md-6">
                                <label class="form-label">{{ __('admin.billing.promotion') }}</label>
                                <select name="promotion_id" class="form-select @error('promotion_id') is-invalid @enderror">
                                    <option value="">{{ __('admin.billing.no_promotion') }}</option>
                                    @foreach ($promotions as $promotion)
                                        <option value="{{ $promotion->id }}" @selected(old('promotion_id', $invoice->promotion_id) == $promotion->id)>
                                            {{ $promotion->title_en ?? $promotion->title_ar }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('promotion_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Discount Type -->
                            <div class="col-md-6">
                                <label class="form-label">{{ __('admin.billing.discount_type') }}</label>
                                <select name="discount_type" class="form-select @error('discount_type') is-invalid @enderror">
                                    <option value="">{{ __('admin.billing.no_discount') }}</option>
                                    @foreach ($discountTypes as $type)
                                        <option value="{{ $type->value }}" @selected(old('discount_type', $invoice->discount_type?->value) === $type->value)>
                                            {{ $type->label() ?? $type->value }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('discount_type')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Discount Value -->
                            <div class="col-md-6">
                                <label class="form-label">{{ __('admin.billing.discount_value') }}</label>
                                <input type="number" name="discount_value" class="form-control @error('discount_value') is-invalid @enderror"
                                       step="0.01" min="0" placeholder="0.00" value="{{ old('discount_value', $invoice->discount_value) }}">
                                @error('discount_value')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Notes -->
                            <div class="col-12">
                                <label class="form-label">{{ __('admin.billing.notes') }}</label>
                                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror"
                                          rows="3" placeholder="{{ __('admin.billing.additional_notes') }}">{{ old('notes', $invoice->notes) }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Line Items Card -->
                @if (isset($isEdit) && $isEdit && $invoice->id)
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">{{ __('admin.billing.items') }}</h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('admin.billing.service') }}</th>
                                        <th>{{ __('admin.billing.quantity') }}</th>
                                        <th>{{ __('admin.billing.unit_price') }}</th>
                                        <th>{{ __('admin.billing.total') }}</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($invoice->items as $item)
                                        <tr>
                                            <td>{{ $item->service?->name_en ?? $item->description }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>{{ number_format($item->unit_price, 2) }}</td>
                                            <td>{{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                                            <td>
                                                @can('invoices.edit')
                                                    <form action="{{ route('admin.billing.invoices.items.destroy', [$invoice, $item]) }}" method="POST" style="display:inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('{{ __('admin.billing.confirm_delete') }}')">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-3">
                                                {{ __('admin.billing.no_line_items') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer">
                            @can('invoices.edit')
                                <button type="button" class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#addItemModal">
                                    <i class="bi bi-plus"></i> {{ __('admin.billing.add_line_item') }}
                                </button>
                            @endcan
                        </div>
                    </div>
                @endif
            </div>

            <!-- Summary Card -->
            <div class="col-lg-4">
                <div class="card sticky-top" style="top: 20px;">
                    <div class="card-header">
                        <h6 class="mb-0">{{ __('admin.billing.summary') }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ __('admin.billing.subtotal') }}:</span>
                            <strong id="subtotal">{{ isset($invoice) ? number_format($invoice->subtotal ?? $invoice->total, 2) : '0.00' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ __('admin.billing.discount') }}:</span>
                            <strong id="discount">-{{ old('discount_value', $invoice->discount_value ?? 0) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>{{ __('admin.billing.tax') }}:</span>
                            <strong id="tax">0.00</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <h6>{{ __('admin.billing.total') }}:</h6>
                            <h5 id="total">{{ isset($invoice) ? number_format($invoice->total, 2) : '0.00' }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle"></i> {{ __('admin.save') }}
            </button>
            <a href="{{ route('admin.billing.invoices.index') }}" class="btn btn-secondary">
                {{ __('admin.cancel') }}
            </a>
        </div>
    </form>
</div>

<!-- Add Line Item Modal (only in edit mode) -->
@if (isset($isEdit) && $isEdit)
    <div class="modal fade" id="addItemModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.billing.invoices.items.store', $invoice) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('admin.billing.add_line_item') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('admin.billing.service') }}<span class="text-danger">*</span></label>
                            <select name="service_id" class="form-select" required>
                                <option value="">{{ __('admin.billing.select_service') }}</option>
                                @foreach (App\Models\Clinic\Service::active()->get() as $service)
                                    <option value="{{ $service->id }}" data-price="{{ $service->default_price }}">
                                        {{ $service->name_en ?? $service->name_ar }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('admin.billing.quantity') }}<span class="text-danger">*</span></label>
                            <input type="number" name="quantity" class="form-control" min="1" value="1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('admin.billing.unit_price') }}<span class="text-danger">*</span></label>
                            <input type="number" name="unit_price" class="form-control" step="0.01" min="0" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('admin.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('admin.billing.add_line_item') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var patientSelect = document.getElementById('invoice_patient_id');
    var visitSelect = document.getElementById('invoice_visit_id');
    if (!patientSelect || !visitSelect) {
        return;
    }

    var urlTemplate = @json($visitsByPatientUrlTemplate);
    var selectedVisitId = @json((string) $selectedVisitId);
    var labels = {
        selectVisit: @json(__('admin.billing.select_visit')),
        loading: @json(__('appointments.form.placeholders.loading')),
        loadFailed: @json(__('appointments.form.placeholders.load_failed')),
    };

    function resetVisitOptions(placeholder, disabled) {
        visitSelect.innerHTML = '';
        var option = document.createElement('option');
        option.value = '';
        option.textContent = placeholder;
        visitSelect.appendChild(option);
        visitSelect.disabled = disabled;
    }

    function formatVisitLabel(visit) {
        var suffix = visit.visit_date ? (' - ' + visit.visit_date) : '';
        return (visit.visit_no || ('Visit #' + visit.id)) + suffix;
    }

    function loadVisits(patientId) {
        if (!patientId) {
            selectedVisitId = '';
            resetVisitOptions(labels.selectVisit, true);
            return;
        }

        resetVisitOptions(labels.loading, true);
        var endpoint = urlTemplate.replace('__PATIENT__', encodeURIComponent(patientId));

        fetch(endpoint, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('failed');
                }
                return response.json();
            })
            .then(function (payload) {
                var visits = Array.isArray(payload.visits) ? payload.visits : [];
                resetVisitOptions(labels.selectVisit, false);

                visits.forEach(function (visit) {
                    var option = document.createElement('option');
                    option.value = String(visit.id);
                    option.textContent = formatVisitLabel(visit);
                    if (selectedVisitId && String(visit.id) === String(selectedVisitId)) {
                        option.selected = true;
                    }
                    visitSelect.appendChild(option);
                });

                selectedVisitId = '';
            })
            .catch(function () {
                resetVisitOptions(labels.loadFailed, true);
            });
    }

    patientSelect.addEventListener('change', function () {
        selectedVisitId = '';
        loadVisits(patientSelect.value);
    });

    if (patientSelect.value && visitSelect.options.length <= 1) {
        loadVisits(patientSelect.value);
    } else if (!patientSelect.value) {
        resetVisitOptions(labels.selectVisit, true);
    }
});
</script>
@endpush

