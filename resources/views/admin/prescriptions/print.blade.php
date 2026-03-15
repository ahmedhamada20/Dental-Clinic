<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Prescription') }} #{{ $prescription->id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none;
            }
            body {
                margin: 0;
            }
        }
        .header-block {
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container py-4" style="max-width: 900px;">
        <div class="no-print mb-3 d-flex gap-2">
            <button type="button" onclick="window.print()" class="btn btn-primary btn-sm">
                <i class="bi bi-printer"></i> {{ __('Print') }}
            </button>
            <a href="{{ route('admin.prescriptions.show', $prescription) }}" class="btn btn-secondary btn-sm">{{ __('admin.back') }}</a>
        </div>

        <div class="header-block">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h4 class="mb-1">{{ __('Prescription') }} #{{ $prescription->id }}</h4>
                    <div class="text-muted">{{ __('Issued at') }}: {{ optional($prescription->issued_at)->format('Y-m-d H:i') ?? 'N/A' }}</div>
                </div>
                <div class="text-end">
                    <div><strong>{{ __('Patient') }}:</strong> {{ $prescription->patient?->full_name ?? 'N/A' }}</div>
                    <div><strong>{{ __('Doctor') }}:</strong> {{ $prescription->doctor?->display_name ?? $prescription->doctor?->full_name ?? 'N/A' }}</div>
                    <div><strong>{{ __('Visit') }}:</strong> {{ $prescription->visit?->visit_no ?? 'N/A' }}</div>
                </div>
            </div>
        </div>

        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>{{ __('Medicine') }}</th>
                    <th>{{ __('Dosage') }}</th>
                    <th>{{ __('Frequency') }}</th>
                    <th>{{ __('admin.prescriptions.dose_duration') }}</th>
                    <th>{{ __('admin.prescriptions.treatment_duration') }}</th>
                    <th>{{ __('Instructions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($prescription->items as $item)
                    <tr>
                        <td>{{ $item->medicine_name }}</td>
                        <td>{{ $item->dosage ?: '-' }}</td>
                        <td>{{ $item->frequency ?: '-' }}</td>
                        <td>{{ $item->dose_duration ?: '-' }}</td>
                        <td>{{ $item->treatment_duration ?: ($item->duration ?: '-') }}</td>
                        <td>{{ $item->instructions ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-3">{{ __('No medicines prescribed') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($prescription->notes)
            <div class="mt-4">
                <h6>{{ __('Notes') }}</h6>
                <p class="mb-0">{{ $prescription->notes }}</p>
            </div>
        @endif
    </div>
</body>
</html>

