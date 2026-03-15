<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('admin.billing.invoice_no') }} - {{ $invoice->invoice_no ?? 'INV-' . $invoice->id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
        .invoice-header {
            border-bottom: 3px solid #0d6efd;
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }
        .invoice-details {
            margin-bottom: 2rem;
        }
        .invoice-table {
            margin-bottom: 2rem;
        }
        .invoice-summary {
            text-align: right;
            margin-top: 2rem;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #dee2e6;
        }
        .summary-row.total {
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            font-weight: bold;
            padding: 1rem 0;
        }
    </style>
</head>
<body>
    <div class="container" style="max-width: 900px; margin-top: 2rem;">
        <!-- No Print Section -->
        <div class="no-print mb-3">
            <button class="btn btn-primary" onclick="window.print()">
                <i class="bi bi-printer"></i> {{ __('admin.billing.print_invoice') }}
            </button>
            <a href="{{ route('admin.billing.invoices.show', $invoice) }}" class="btn btn-secondary">
                {{ __('admin.back') }}
            </a>
        </div>

        <!-- Invoice Header -->
        <div class="invoice-header">
            <div class="row">
                <div class="col-md-6">
                    <h2 class="mb-0">{{ __('admin.billing.title') }}</h2>
                    <p class="text-muted mb-0">{{ __('admin.billing.invoice_no') }}: {{ $invoice->invoice_no ?? 'INV-' . $invoice->id }}</p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="mb-1"><strong>{{ __('admin.billing.issued_at') }}:</strong> {{ optional($invoice->issued_at)->format('Y-m-d H:i') ?? 'N/A' }}</p>
                    <p class="mb-0"><strong>{{ __('admin.billing.due_date') }}:</strong> {{ optional($invoice->due_date)->format('Y-m-d') ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        <!-- Invoice Details -->
        <div class="invoice-details">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-muted">{{ __('admin.billing.billing_from') }}</h6>
                    <p class="mb-0">
                        <strong>{{ \App\Models\Clinic\ClinicSetting::getValue('clinic_name', 'Dental Clinic') }}</strong><br>
                        {{ \App\Models\Clinic\ClinicSetting::getValue('address') }}<br>
                        {{ __('Phone') }}: {{ \App\Models\Clinic\ClinicSetting::getValue('phone') }}<br>
                        {{ __('Email') }}: {{ \App\Models\Clinic\ClinicSetting::getValue('email') }}
                    </p>
                </div>
                <div class="col-md-6">
                    <h6 class="text-muted">{{ __('admin.billing.billing_to') }}</h6>
                    @if ($invoice->patient)
                        <p class="mb-0">
                            <strong>{{ $invoice->patient->full_name }}</strong><br>
                            {{ __('ID') }}: {{ $invoice->patient->id }}<br>
                            {{ __('Phone') }}: {{ $invoice->patient->phone ?? 'N/A' }}<br>
                            {{ __('Email') }}: {{ $invoice->patient->email ?? 'N/A' }}
                        </p>
                    @else
                        <p class="mb-0">N/A</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Invoice Items -->
        <div class="invoice-table">
            <table class="table table-bordered">
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
                            <td colspan="4" class="text-center text-muted">{{ __('No line items') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Invoice Summary -->
        <div class="row">
            <div class="col-md-6 offset-md-6">
                <div class="invoice-summary">
                    <div class="summary-row">
                        <span>{{ __('admin.billing.subtotal') }}:</span>
                        <span>{{ number_format($invoice->subtotal ?? $invoice->total, 2) }}</span>
                    </div>
                    @if ($invoice->discount_value)
                        <div class="summary-row">
                            <span>{{ __('admin.billing.discount') }}:</span>
                            <span>-{{ number_format($invoice->discount_value, 2) }}</span>
                        </div>
                    @endif
                    <div class="summary-row total">
                        <span>{{ __('admin.billing.total') }}:</span>
                        <span>{{ number_format($invoice->total, 2) }}</span>
                    </div>
                    <div class="summary-row mt-3">
                        <span>{{ __('admin.billing.paid_amount') }}:</span>
                        <span>{{ number_format($invoice->paid_amount, 2) }}</span>
                    </div>
                    <div class="summary-row">
                        <span>{{ __('admin.billing.remaining_amount') }}:</span>
                        <span>{{ number_format($invoice->remaining_amount ?? ($invoice->total - $invoice->paid_amount), 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice Notes -->
        @if ($invoice->notes)
            <div class="mt-4 pt-4 border-top">
                <h6>{{ __('admin.billing.notes') }}</h6>
                <p>{{ $invoice->notes }}</p>
            </div>
        @endif

        <!-- Payments List -->
        @if ($invoice->payments->count() > 0)
            <div class="mt-4 pt-4 border-top">
                <h6>{{ __('admin.billing.payments') }}</h6>
                <table class="table table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('admin.billing.payment_date') }}</th>
                            <th>{{ __('admin.billing.payment_method') }}</th>
                            <th class="text-end">{{ __('admin.billing.amount') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoice->payments as $payment)
                            <tr>
                                <td>{{ optional($payment->payment_date)->format('Y-m-d') }}</td>
                                <td>{{ $payment->payment_method?->label() ?? str_replace('_', ' ', ucfirst($payment->payment_method)) }}</td>
                                <td class="text-end">{{ number_format($payment->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Footer -->
        <div class="mt-5 pt-4 border-top text-center text-muted small">
            <p class="mb-0">{{ __('admin.billing.thank_you') }}</p>
            <p class="mb-0">{{ __('Generated on') }} {{ now()->format('Y-m-d H:i:s') }}</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

