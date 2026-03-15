@extends('admin.layouts.app')

@section('title', __('admin.reports.title'))

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">{{ __('admin.sidebar.dashboard') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('admin.reports.title') }}</li>
        </ol>
    </nav>
@endsection

@section('content')
@php
    $canViewFinancial = (bool) ($canViewFinancial ?? false);
@endphp
<div class="container-fluid">
    <form method="GET" class="card card-body mb-3">
        <div class="row g-2">
            <div class="col-md-2">
                <label class="form-label">{{ __('admin.reports.from') }}</label>
                <input type="date" class="form-control" name="from_date" value="{{ $fromDate }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">{{ __('admin.reports.to') }}</label>
                <input type="date" class="form-control" name="to_date" value="{{ $toDate }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">{{ __('admin.reports.specialty') }}</label>
                <select name="specialty_id" class="form-select">
                    <option value="">{{ __('admin.reports.all_specialties') }}</option>
                    @foreach ($specialties as $specialty)
                        <option value="{{ $specialty->id }}" @selected((string) $specialtyId === (string) $specialty->id)>{{ $specialty->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">{{ __('admin.reports.doctor') }}</label>
                <select name="doctor_id" class="form-select">
                    <option value="">{{ __('admin.reports.all_doctors') }}</option>
                    @foreach ($doctors as $doctor)
                        <option value="{{ $doctor->id }}" @selected((string) $doctorId === (string) $doctor->id)>{{ $doctor->display_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">{{ __('admin.reports.status') }}</label>
                <select name="status" class="form-select">
                    <option value="">{{ __('admin.reports.all_statuses') }}</option>
                    <option value="pending" @selected($status === 'pending')>{{ __('admin.reports.statuses.pending') }}</option>
                    <option value="completed" @selected($status === 'completed')>{{ __('admin.reports.statuses.completed') }}</option>
                    <option value="cancelled_by_clinic" @selected($status === 'cancelled_by_clinic')>{{ __('admin.reports.statuses.cancelled_by_clinic') }}</option>
                    <option value="cancelled_by_patient" @selected($status === 'cancelled_by_patient')>{{ __('admin.reports.statuses.cancelled_by_patient') }}</option>
                    <option value="no_show" @selected($status === 'no_show')>{{ __('admin.reports.statuses.no_show') }}</option>
                    <option value="paid" @selected($status === 'paid')>{{ __('admin.reports.statuses.paid') }}</option>
                    <option value="partially_paid" @selected($status === 'partially_paid')>{{ __('admin.reports.statuses.partially_paid') }}</option>
                    <option value="unpaid" @selected($status === 'unpaid')>{{ __('admin.reports.statuses.unpaid') }}</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-primary w-100">{{ __('admin.reports.apply') }}</button>
            </div>
        </div>
    </form>

    {{-- Hidden form used by all Excel export buttons --}}
    <form id="excel-export-form" method="POST" action="{{ route('admin.reports.export-excel') }}" style="display:none;">
        @csrf
        <input type="hidden" name="from_date" value="{{ $fromDate }}">
        <input type="hidden" name="to_date" value="{{ $toDate }}">
        <input type="hidden" name="specialty_id" value="{{ $specialtyId }}">
        <input type="hidden" name="doctor_id" value="{{ $doctorId }}">
        <input type="hidden" name="status" value="{{ $status }}">
        <input type="hidden" name="report_type" id="excel-report-type" value="">
    </form>

    {{-- Excel Export Section --}}
    <div class="card mb-3">
        <div class="card-header d-flex align-items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="text-success" viewBox="0 0 16 16">
                <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>
                <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/>
            </svg>
            <strong>{{ __('admin.reports.export.title') }}</strong>
            <small class="text-muted ms-1">{{ __('admin.reports.export.subtitle') }}</small>
        </div>
        <div class="card-body">
            <div class="row g-2">
                @php
                    $exportTypes = [
                        'appointments' => ['label' => __('admin.reports.export.types.appointments'), 'icon' => '📅'],
                        'revenue' => ['label' => __('admin.reports.export.types.revenue'), 'icon' => '💰'],
                        'invoices' => ['label' => __('admin.reports.export.types.invoices'), 'icon' => '🧾'],
                        'patients' => ['label' => __('admin.reports.export.types.patients'), 'icon' => '👤'],
                        'services' => ['label' => __('admin.reports.export.types.services'), 'icon' => '🔧'],
                        'doctors' => ['label' => __('admin.reports.export.types.doctors'), 'icon' => '🩺'],
                        'promotions' => ['label' => __('admin.reports.export.types.promotions'), 'icon' => '🎁'],
                        'audit_logs' => ['label' => __('admin.reports.export.types.audit_logs'), 'icon' => '📋'],
                    ];

                    if (! $canViewFinancial) {
                        unset($exportTypes['revenue'], $exportTypes['invoices']);
                    }
                @endphp

                @foreach ($exportTypes as $type => $meta)
                    <div class="col-6 col-md-3 col-lg-2">
                        <button type="button"
                                class="btn btn-outline-success w-100 py-2 d-flex flex-column align-items-center gap-1"
                                onclick="document.getElementById('excel-report-type').value='{{ $type }}'; document.getElementById('excel-export-form').submit();">
                            <span style="font-size:1.4rem;">{{ $meta['icon'] }}</span>
                            <span class="small fw-semibold">{{ $meta['label'] }}</span>
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        @if ($canViewFinancial)
            <div class="col-md-3">
                <div class="card h-100"><div class="card-body"><small class="text-muted d-block">{{ __('admin.reports.metrics.total_revenue') }}</small><h4 class="mb-0">{{ number_format((float) ($revenueData['total_revenue'] ?? 0), 2) }}</h4></div></div>
            </div>
        @endif
        <div class="col-md-3">
            <div class="card h-100"><div class="card-body"><small class="text-muted d-block">{{ __('admin.reports.metrics.total_appointments') }}</small><h4 class="mb-0">{{ number_format((int) ($appointmentsData['total_appointments'] ?? 0)) }}</h4></div></div>
        </div>
        <div class="col-md-3">
            <div class="card h-100"><div class="card-body"><small class="text-muted d-block">{{ __('admin.reports.metrics.active_doctors') }}</small><h4 class="mb-0">{{ number_format($doctorsBySpecialty->sum('doctors_count')) }}</h4></div></div>
        </div>
        <div class="col-md-3">
            <div class="card h-100"><div class="card-body"><small class="text-muted d-block">{{ __('admin.reports.metrics.daily_workload_rows') }}</small><h4 class="mb-0">{{ number_format($dailyWorkloadBySpecialty->count()) }}</h4></div></div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">{{ __('admin.reports.sections.appointments_by_specialty') }}</div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>{{ __('admin.reports.specialty') }}</th><th class="text-end">{{ __('admin.reports.appointments') }}</th></tr></thead>
                        <tbody>
                        @forelse($appointmentsBySpecialty as $row)
                            <tr><td>{{ $row->specialty_name }}</td><td class="text-end">{{ number_format($row->appointments_count) }}</td></tr>
                        @empty
                            <tr><td colspan="2" class="text-center py-3">{{ __('admin.reports.no_data') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">{{ __('admin.reports.sections.doctors_by_specialty') }}</div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>{{ __('admin.reports.specialty') }}</th><th class="text-end">{{ __('admin.reports.doctors') }}</th></tr></thead>
                        <tbody>
                        @forelse($doctorsBySpecialty as $row)
                            <tr><td>{{ $row->specialty_name }}</td><td class="text-end">{{ number_format($row->doctors_count) }}</td></tr>
                        @empty
                            <tr><td colspan="2" class="text-center py-3">{{ __('admin.reports.no_data') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if ($canViewFinancial)
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">{{ __('admin.reports.sections.revenue_by_specialty') }}</div>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>{{ __('admin.reports.specialty') }}</th><th class="text-end">{{ __('admin.reports.revenue') }}</th></tr></thead>
                            <tbody>
                            @forelse($revenueBySpecialty as $row)
                                <tr><td>{{ $row->specialty_name }}</td><td class="text-end">{{ number_format((float) $row->revenue_total, 2) }}</td></tr>
                            @empty
                                <tr><td colspan="2" class="text-center py-3">{{ __('admin.reports.no_data') }}</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">{{ __('admin.reports.sections.daily_workload_by_specialty') }}</div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('admin.reports.date') }}</th>
                                <th>{{ __('admin.reports.specialty') }}</th>
                                <th class="text-end">{{ __('admin.reports.appointments') }}</th>
                                <th class="text-end">{{ __('admin.reports.minutes') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($dailyWorkloadBySpecialty as $row)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($row->workload_date)->format('Y-m-d') }}</td>
                                <td>{{ $row->specialty_name }}</td>
                                <td class="text-end">{{ number_format($row->appointments_count) }}</td>
                                <td class="text-end">{{ number_format($row->total_minutes) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-3">{{ __('admin.reports.no_data') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

