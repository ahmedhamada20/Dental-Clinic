@extends('admin.layouts.app')

@section('title', __('Create Invoice'))

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.dashboard.index') }}">{{ __('Dashboard') }}</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('admin.billing.index') }}">{{ __('Billing') }}</a>
            </li>
            <li class="breadcrumb-item active">{{ __('Create Invoice') }}</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>{{ __('Create New Invoice') }}</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.billing.invoices.store') }}" id="invoiceForm" novalidate>
                        @csrf

                        <!-- Invoice Number and Date -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="invoice_no" class="form-label">{{ __('Invoice Number') }} <span class="text-danger">*</span></label>
                                <input type="text" name="invoice_no" id="invoice_no" class="form-control @error('invoice_no') is-invalid @enderror" value="{{ old('invoice_no', $nextInvoiceNo ?? '') }}" readonly>
                                @error('invoice_no')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="invoice_date" class="form-label">{{ __('Invoice Date') }} <span class="text-danger">*</span></label>
                                <input type="date" name="invoice_date" id="invoice_date" class="form-control @error('invoice_date') is-invalid @enderror" value="{{ old('invoice_date', now()->format('Y-m-d')) }}" required>
                                @error('invoice_date')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Patient Selection -->
                        <div class="mb-3">
                            <label for="patient_id" class="form-label">{{ __('Patient') }} <span class="text-danger">*</span></label>
                            <select name="patient_id" id="patient_id" class="form-select @error('patient_id') is-invalid @enderror" required>
                                <option value="">{{ __('Select a patient...') }}</option>
                                @foreach($patients ?? [] as $patient)
                                    <option value="{{ $patient->id }}" @selected(old('patient_id') == $patient->id)>
                                        {{ $patient->name }} ({{ $patient->phone }})
                                    </option>
                                @endforeach
                            </select>
                            @error('patient_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Due Date -->
                        <div class="mb-3">
                            <label for="due_date" class="form-label">{{ __('Due Date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="due_date" id="due_date" class="form-control @error('due_date') is-invalid @enderror" value="{{ old('due_date', now()->addDays(30)->format('Y-m-d')) }}" required>
                            @error('due_date')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">{{ __('Description / Notes') }}</label>
                            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Invoice Items -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">{{ __('Invoice Items') }}</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="addItemBtn">
                                    <i class="bi bi-plus"></i> {{ __('Add Item') }}
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm mb-0" id="itemsTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>{{ __('Description') }}</th>
                                            <th style="width: 100px;">{{ __('Quantity') }}</th>
                                            <th style="width: 100px;">{{ __('Unit Price') }}</th>
                                            <th style="width: 100px;">{{ __('Amount') }}</th>
                                            <th style="width: 50px;">{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsBody">
                                        <tr class="item-row">
                                            <td><input type="text" name="items[0][description]" class="form-control form-control-sm item-description" placeholder="{{ __('Item description') }}" value="{{ old('items.0.description') }}"></td>
                                            <td><input type="number" name="items[0][quantity]" class="form-control form-control-sm item-quantity" value="{{ old('items.0.quantity', 1) }}" min="1" step="1"></td>
                                            <td><input type="number" name="items[0][unit_price]" class="form-control form-control-sm item-price" value="{{ old('items.0.unit_price') }}" min="0" step="0.01"></td>
                                            <td class="item-amount">$0.00</td>
                                            <td><button type="button" class="btn btn-sm btn-outline-danger removeItemBtn"><i class="bi bi-trash"></i></button></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Totals -->
                        <div class="row mb-3">
                            <div class="col-md-6"></div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="row mb-2">
                                            <div class="col-md-8">{{ __('Subtotal:') }}</div>
                                            <div class="col-md-4 text-end"><span id="subtotal">$0.00</span></div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-md-8">
                                                <label for="tax_rate">{{ __('Tax (%):') }}</label>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <input type="number" name="tax_rate" id="tax_rate" class="form-control form-control-sm" value="{{ old('tax_rate', 0) }}" min="0" max="100" step="0.01">
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-md-8">{{ __('Tax Amount:') }}</div>
                                            <div class="col-md-4 text-end"><span id="taxAmount">$0.00</span></div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-8"><strong>{{ __('Total:') }}</strong></div>
                                            <div class="col-md-4 text-end"><h5 class="mb-0"><strong id="total">$0.00</strong></h5></div>
                                        </div>
                                        <input type="hidden" name="total" id="totalInput" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check"></i> {{ __('Create Invoice') }}
                            </button>
                            <a href="{{ route('admin.billing.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> {{ __('Cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>{{ __('Invoice Tips') }}</h6>
                </div>
                <div class="card-body small">
                    <ul class="mb-0">
                        <li>{{ __('Invoice number is auto-generated') }}</li>
                        <li>{{ __('Select patient for automatic details') }}</li>
                        <li>{{ __('Add line items for services/products') }}</li>
                        <li>{{ __('Tax calculated automatically') }}</li>
                        <li>{{ __('Save as draft or send directly') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let itemCount = 1;

    const addItemBtn = document.getElementById('addItemBtn');
    const itemsBody = document.getElementById('itemsBody');
    const taxRateInput = document.getElementById('tax_rate');

    function calculateTotals() {
        let subtotal = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.item-quantity').value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            const amount = qty * price;
            row.querySelector('.item-amount').textContent = '$' + amount.toFixed(2);
            subtotal += amount;
        });

        const taxRate = parseFloat(taxRateInput.value) || 0;
        const taxAmount = subtotal * (taxRate / 100);
        const total = subtotal + taxAmount;

        document.getElementById('subtotal').textContent = '$' + subtotal.toFixed(2);
        document.getElementById('taxAmount').textContent = '$' + taxAmount.toFixed(2);
        document.getElementById('total').textContent = '$' + total.toFixed(2);
        document.getElementById('totalInput').value = total.toFixed(2);
    }

    // Add item button
    addItemBtn.addEventListener('click', function() {
        const newRow = document.createElement('tr');
        newRow.className = 'item-row';
        newRow.innerHTML = `
            <td><input type="text" name="items[${itemCount}][description]" class="form-control form-control-sm item-description" placeholder="Item description"></td>
            <td><input type="number" name="items[${itemCount}][quantity]" class="form-control form-control-sm item-quantity" value="1" min="1" step="1"></td>
            <td><input type="number" name="items[${itemCount}][unit_price]" class="form-control form-control-sm item-price" value="0" min="0" step="0.01"></td>
            <td class="item-amount">$0.00</td>
            <td><button type="button" class="btn btn-sm btn-outline-danger removeItemBtn"><i class="bi bi-trash"></i></button></td>
        `;
        itemsBody.appendChild(newRow);
        itemCount++;

        // Attach event listeners to new inputs
        newRow.querySelectorAll('.item-quantity, .item-price').forEach(input => {
            input.addEventListener('change', calculateTotals);
        });

        // Attach remove button
        newRow.querySelector('.removeItemBtn').addEventListener('click', function() {
            newRow.remove();
            calculateTotals();
        });
    });

    // Initial event listeners
    document.querySelectorAll('.item-quantity, .item-price').forEach(input => {
        input.addEventListener('change', calculateTotals);
    });

    document.querySelectorAll('.removeItemBtn').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.item-row').remove();
            calculateTotals();
        });
    });

    taxRateInput.addEventListener('change', calculateTotals);

    // Calculate on load
    calculateTotals();

    // Form validation
    const form = document.getElementById('invoiceForm');
    form.addEventListener('submit', function(event) {
        if (!this.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        this.classList.add('was-validated');
    });
});
</script>
@endpush

