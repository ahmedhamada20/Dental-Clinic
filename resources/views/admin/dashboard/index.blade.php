@extends('admin.layouts.app')

@section('title', __('dashboard.title'))

@section('breadcrumb')
    <nav aria-label="{{ __('admin.layout.breadcrumb_label') }}">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">{{ __('dashboard.title') }}</li>
        </ol>
    </nav>
@endsection

@section('content')
@php
    $currency = __('dashboard.currency_symbol');
    $canViewFinancial = (bool) ($canViewFinancial ?? false);
    $chartLabels = $appointmentsBySpecialty->pluck('specialty_name')->take(7)->values();
    $chartAppointmentValues = $appointmentsBySpecialty->pluck('appointments_count')->take(7)->values();
    $chartRevenueLabels = $revenueBySpecialty->pluck('specialty_name')->take(6)->values();
    $chartRevenueValues = $revenueBySpecialty->pluck('revenue_total')->map(fn ($value) => (float) $value)->take(6)->values();
    $chartDoctorLabels = $doctorsBySpecialty->pluck('specialty_name')->take(6)->values();
    $chartDoctorValues = $doctorsBySpecialty->pluck('doctors_count')->take(6)->values();
@endphp

<div class="container-fluid dashboard-v2">
    <div class="dashboard-hero card border-0 shadow-sm mb-4">
        <div class="card-body p-4 p-lg-5">
            <div class="row g-4 align-items-center">
                <div class="col-lg-8">
                    <span class="badge rounded-pill text-bg-light mb-3">{{ __('dashboard.hero.badge') }}</span>
                    <h1 class="h2 fw-bold mb-2">{{ __('dashboard.hero.title') }}</h1>
                    <p class="text-secondary mb-4">{{ __('dashboard.hero.subtitle') }}</p>
                    <div class="d-flex flex-wrap gap-2">
                        @if (Route::has('admin.appointments.create'))
                            <a href="{{ route('admin.appointments.create') }}" class="btn btn-primary">
                                <i class="bi bi-calendar-plus me-1"></i>{{ __('dashboard.actions.new_appointment') }}
                            </a>
                        @endif
                        @if (Route::has('admin.patients.create'))
                            <a href="{{ route('admin.patients.create') }}" class="btn btn-outline-primary">
                                <i class="bi bi-person-plus me-1"></i>{{ __('dashboard.actions.new_patient') }}
                            </a>
                        @endif
                        @if (Route::has('admin.reports.index'))
                            <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-bar-chart me-1"></i>{{ __('dashboard.actions.open_reports') }}
                            </a>
                        @endif
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="dashboard-hero-panel">
                        @if ($canViewFinancial)
                            <div class="small text-uppercase text-muted mb-1">{{ __('dashboard.hero.monthly_revenue') }}</div>
                            <div class="display-6 fw-bold">{{ $currency }}{{ number_format($monthlyRevenue, 2) }}</div>
                            <div class="small text-muted">{{ __('dashboard.hero.today_revenue') }}: {{ $currency }}{{ number_format($todayRevenue, 2) }}</div>
                        @else
                            <div class="small text-uppercase text-muted mb-1">{{ __('dashboard.title') }}</div>
                            <div class="h5 fw-semibold mb-1">{{ __('dashboard.stats.today_appointments') }}: {{ number_format($todayAppointments) }}</div>
                            <div class="small text-muted">{{ __('dashboard.stats.waiting_list') }}: {{ number_format($waitingListRequests) }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg">
            <div class="card border-0 shadow-sm h-100 stat-card">
                <div class="card-body">
                    <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-people"></i></div>
                    <div class="small text-muted">{{ __('dashboard.stats.patients') }}</div>
                    <div class="h4 mb-0">{{ number_format($totalPatients) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="card border-0 shadow-sm h-100 stat-card">
                <div class="card-body">
                    <div class="stat-icon bg-info-subtle text-info"><i class="bi bi-calendar-check"></i></div>
                    <div class="small text-muted">{{ __('dashboard.stats.today_appointments') }}</div>
                    <div class="h4 mb-0">{{ number_format($todayAppointments) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="card border-0 shadow-sm h-100 stat-card">
                <div class="card-body">
                    <div class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-hourglass-split"></i></div>
                    <div class="small text-muted">{{ __('dashboard.stats.waiting_list') }}</div>
                    <div class="h4 mb-0">{{ number_format($waitingListRequests) }}</div>
                </div>
            </div>
        </div>
        @if ($canViewFinancial)
            <div class="col-6 col-lg">
                <div class="card border-0 shadow-sm h-100 stat-card">
                    <div class="card-body">
                        <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-cash-stack"></i></div>
                        <div class="small text-muted">{{ __('dashboard.stats.today_revenue') }}</div>
                        <div class="h4 mb-0">{{ $currency }}{{ number_format($todayRevenue, 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg">
                <div class="card border-0 shadow-sm h-100 stat-card">
                    <div class="card-body">
                        <div class="stat-icon bg-secondary-subtle text-secondary"><i class="bi bi-graph-up-arrow"></i></div>
                        <div class="small text-muted">{{ __('dashboard.stats.monthly_revenue') }}</div>
                        <div class="h4 mb-0">{{ $currency }}{{ number_format($monthlyRevenue, 2) }}</div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="row g-3 mb-4">
        <div class="{{ $canViewFinancial ? 'col-lg-8' : 'col-lg-12' }}">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pb-0">
                    <h5 class="mb-1">{{ __('dashboard.charts.appointments_title') }}</h5>
                    <p class="text-muted small mb-0">{{ __('dashboard.charts.appointments_subtitle') }}</p>
                </div>
                <div class="card-body">
                    <canvas id="appointmentsBySpecialtyChart" height="120"></canvas>
                </div>
            </div>
        </div>
        @if ($canViewFinancial)
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 pb-0">
                        <h5 class="mb-1">{{ __('dashboard.charts.revenue_title') }}</h5>
                        <p class="text-muted small mb-0">{{ __('dashboard.charts.revenue_subtitle') }}</p>
                    </div>
                    <div class="card-body">
                        <canvas id="revenueBySpecialtyChart" height="220"></canvas>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('dashboard.recent_appointments') }}</h5>
                    @if (Route::has('admin.appointments.index'))
                        <a href="{{ route('admin.appointments.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('dashboard.actions.view_all') }}</a>
                    @endif
                </div>
                <ul class="list-group list-group-flush">
                    @forelse ($recentAppointments as $item)
                        @php
                            $statusValue = (string) ($item->status_value ?? $item->status ?? 'pending');
                            $statusKey = 'appointments.status.' . $statusValue;
                            $statusText = __($statusKey);
                            $statusText = $statusText === $statusKey ? ucfirst($statusValue) : $statusText;
                            $statusBadgeClass = match ($statusValue) {
                                'completed' => 'text-bg-success',
                                'confirmed', 'checked_in', 'in_progress' => 'text-bg-primary',
                                'cancelled', 'cancelled_by_patient', 'cancelled_by_clinic', 'no_show' => 'text-bg-danger',
                                default => 'text-bg-secondary',
                            };
                        @endphp
                        <li class="list-group-item d-flex justify-content-between align-items-center gap-2">
                            <div>
                                <div class="fw-semibold">{{ $item->patient_name }}</div>
                                <div class="small text-muted">{{ $item->doctor ?? __('common.not_available') }}</div>
                            </div>
                            <div class="text-end">
                                <div class="small">{{ $item->appointment_time }}</div>
                                <span class="badge {{ $statusBadgeClass }}">{{ $statusText }}</span>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">{{ __('dashboard.empty.recent_appointments') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('dashboard.latest_patients') }}</h5>
                    @if (Route::has('admin.patients.index'))
                        <a href="{{ route('admin.patients.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('dashboard.actions.view_all') }}</a>
                    @endif
                </div>
                <ul class="list-group list-group-flush">
                    @forelse ($latestPatients as $patient)
                        <li class="list-group-item d-flex justify-content-between align-items-center gap-2">
                            <div>
                                <div class="fw-semibold">{{ $patient->name }}</div>
                                <div class="small text-muted">{{ $patient->phone }}</div>
                            </div>
                            <div class="small text-muted">{{ isset($patient->created_at) ? $patient->created_at->diffForHumans() : '' }}</div>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">{{ __('dashboard.empty.patients') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="{{ $canViewFinancial ? 'col-lg-6' : 'col-lg-12' }}">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pb-0">
                    <h5 class="mb-1">{{ __('dashboard.charts.doctors_title') }}</h5>
                    <p class="text-muted small mb-0">{{ __('dashboard.charts.doctors_subtitle') }}</p>
                </div>
                <div class="card-body">
                    <canvas id="doctorsBySpecialtyChart" height="140"></canvas>
                </div>
            </div>
        </div>
        @if ($canViewFinancial)
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ __('dashboard.recent_invoices') }}</h5>
                        @if (Route::has('admin.billing.invoices.index'))
                            <a href="{{ route('admin.billing.invoices.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('dashboard.actions.view_all') }}</a>
                        @endif
                    </div>
                    <ul class="list-group list-group-flush">
                        @forelse ($recentInvoices as $invoice)
                            @php
                                $invoiceStatus = (string) ($invoice->status_value ?? $invoice->status ?? 'pending');
                                $invoiceBadgeClass = match ($invoiceStatus) {
                                    'paid' => 'text-bg-success',
                                    'partially_paid' => 'text-bg-warning',
                                    'overdue', 'cancelled', 'void' => 'text-bg-danger',
                                    default => 'text-bg-secondary',
                                };
                            @endphp
                            <li class="list-group-item d-flex justify-content-between align-items-center gap-2">
                                <div>
                                    <div class="fw-semibold">{{ $invoice->invoice_no }}</div>
                                    <div class="small text-muted">{{ $invoice->patient_name }}</div>
                                </div>
                                <div class="text-end">
                                    <div class="small fw-semibold">{{ $currency }}{{ number_format((float) ($invoice->total ?? 0), 2) }}</div>
                                    <span class="badge {{ $invoiceBadgeClass }}">{{ str($invoiceStatus)->replace('_', ' ')->title() }}</span>
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">{{ __('dashboard.empty.invoices') }}</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        @endif
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <h5 class="mb-0">{{ __('dashboard.features.title') }}</h5>
                <span class="text-muted small">{{ __('dashboard.features.subtitle') }}</span>
            </div>
            <div class="row g-3">
                <div class="col-md-6 col-xl-3">
                    <a href="{{ route('admin.appointments.index') }}" class="feature-card text-decoration-none text-reset">
                        <div class="feature-icon"><i class="bi bi-calendar2-week"></i></div>
                        <h6 class="mb-1">{{ __('dashboard.features.appointments.title') }}</h6>
                        <p class="small text-muted mb-0">{{ __('dashboard.features.appointments.description') }}</p>
                    </a>
                </div>
                <div class="col-md-6 col-xl-3">
                    <a href="{{ route('admin.visits.index') }}" class="feature-card text-decoration-none text-reset">
                        <div class="feature-icon"><i class="bi bi-clipboard2-pulse"></i></div>
                        <h6 class="mb-1">{{ __('dashboard.features.visits.title') }}</h6>
                        <p class="small text-muted mb-0">{{ __('dashboard.features.visits.description') }}</p>
                    </a>
                </div>
                <div class="col-md-6 col-xl-3">
                    <a href="{{ route('admin.patients.index') }}" class="feature-card text-decoration-none text-reset">
                        <div class="feature-icon"><i class="bi bi-person-vcard"></i></div>
                        <h6 class="mb-1">{{ __('dashboard.features.records.title') }}</h6>
                        <p class="small text-muted mb-0">{{ __('dashboard.features.records.description') }}</p>
                    </a>
                </div>
                <div class="col-md-6 col-xl-3">
                    <a href="{{ route('admin.reports.index') }}" class="feature-card text-decoration-none text-reset">
                        <div class="feature-icon"><i class="bi bi-file-earmark-bar-graph"></i></div>
                        <h6 class="mb-1">{{ __('dashboard.features.reports.title') }}</h6>
                        <p class="small text-muted mb-0">{{ __('dashboard.features.reports.description') }}</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .dashboard-hero {
        background: linear-gradient(120deg, #f8fbff 0%, #eef4ff 45%, #f8fffb 100%);
    }
    .dashboard-hero-panel {
        border: 1px solid #dfe7ff;
        border-radius: 12px;
        padding: 1rem;
        background: #fff;
    }
    .stat-card .card-body {
        position: relative;
        padding-top: 2.6rem;
    }
    .stat-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        position: absolute;
        top: 1rem;
        right: 1rem;
    }
    .feature-card {
        display: block;
        border: 1px solid #edf0f4;
        border-radius: 12px;
        padding: 1rem;
        height: 100%;
        transition: .2s ease;
    }
    .feature-card:hover {
        border-color: #cfd9ff;
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(17, 24, 39, .06);
    }
    .feature-icon {
        width: 36px;
        height: 36px;
        border-radius: 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #f3f6ff;
        color: #4b63d1;
        margin-bottom: .75rem;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var appointmentsCanvas = document.getElementById('appointmentsBySpecialtyChart');
    var revenueCanvas = document.getElementById('revenueBySpecialtyChart');
    var doctorsCanvas = document.getElementById('doctorsBySpecialtyChart');

    if (typeof Chart === 'undefined') {
        return;
    }

    if (appointmentsCanvas) {
        new Chart(appointmentsCanvas, {
            type: 'bar',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: '{{ __('dashboard.tables.appointments') }}',
                    data: @json($chartAppointmentValues),
                    backgroundColor: '#4b63d1',
                    borderRadius: 8,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }

    if (revenueCanvas) {
        new Chart(revenueCanvas, {
            type: 'doughnut',
            data: {
                labels: @json($chartRevenueLabels),
                datasets: [{
                    data: @json($chartRevenueValues),
                    backgroundColor: ['#4b63d1', '#00a6a6', '#ffb74d', '#66bb6a', '#ab47bc', '#ef5350'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }

    if (doctorsCanvas) {
        new Chart(doctorsCanvas, {
            type: 'line',
            data: {
                labels: @json($chartDoctorLabels),
                datasets: [{
                    label: '{{ __('dashboard.tables.doctors') }}',
                    data: @json($chartDoctorValues),
                    borderColor: '#00897b',
                    backgroundColor: 'rgba(0, 137, 123, .12)',
                    fill: true,
                    tension: .35,
                    pointRadius: 3
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }
});
</script>
@endpush

