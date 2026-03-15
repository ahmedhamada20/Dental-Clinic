@extends('admin.layouts.app')

@section('title', __('patients.title'))

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">{{ __('patients.index.heading') }}</h1>
            <p class="text-muted mb-0">{{ __('patients.index.subtitle') }}</p>
        </div>
        @can('patients.create')
            <a href="{{ route('admin.patients.create') }}" class="btn btn-primary">{{ __('patients.index.new_record') }}</a>
        @endcan
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted small">{{ __('patients.index.stats.total') }}</div><div class="fs-3 fw-bold">{{ $summary['total'] }}</div></div></div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted small">{{ __('patients.index.stats.active') }}</div><div class="fs-3 fw-bold text-success">{{ $summary['active'] }}</div></div></div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted small">{{ __('patients.index.stats.inactive') }}</div><div class="fs-3 fw-bold text-secondary">{{ $summary['inactive'] }}</div></div></div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted small">{{ __('patients.index.stats.important_alerts') }}</div><div class="fs-3 fw-bold text-danger">{{ $summary['withAlerts'] }}</div></div></div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end mb-4">
                <div class="col-md-6">
                    <label class="form-label">{{ __('common.search') }}</label>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="{{ __('patients.index.filters.search_placeholder') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('common.status') }}</label>
                    <select name="status" class="form-select">
                        <option value="">{{ __('patients.index.filters.all_statuses') }}</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ method_exists($status, 'label') ? $status->label() : ucfirst($status->value) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary flex-fill">{{ __('common.filter') }}</button>
                    <a href="{{ route('admin.patients.index') }}" class="btn btn-outline-secondary">{{ __('common.reset') }}</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>{{ __('patients.index.table.patient') }}</th>
                            <th>{{ __('patients.index.table.contact') }}</th>
                            <th>{{ __('patients.index.table.medical_record') }}</th>
                            <th>{{ __('patients.index.table.activity') }}</th>
                            <th>{{ __('common.status') }}</th>
                            <th class="text-end">{{ __('common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($patients as $patient)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $patient->display_name }}</div>
                                    <div class="text-muted small">{{ $patient->patient_code }}</div>
                                </td>
                                <td>
                                    <div>{{ $patient->phone }}</div>
                                    <div class="text-muted small">{{ $patient->email ?: __('patients.index.no_email') }}</div>
                                </td>
                                <td>
                                    <div class="small">{{ __('patients.index.record.contacts', ['count' => $patient->emergencyContacts->count()]) }}</div>
                                    <div class="small">{{ __('patients.index.record.files', ['count' => $patient->medicalFiles->count()]) }}</div>
                                    <div class="small text-danger">{{ __('patients.index.record.allergies', ['status' => filled($patient->medicalHistory?->allergies) ? __('patients.index.record.recorded') : __('patients.index.record.not_recorded')]) }}</div>
                                </td>
                                <td>
                                    <div class="small">{{ __('patients.index.activity.appointments', ['count' => $patient->appointments->count()]) }}</div>
                                    <div class="small">{{ __('patients.index.activity.invoices', ['count' => $patient->invoices->count()]) }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $patient->status?->value === 'active' ? 'success' : 'secondary' }}">{{ $patient->status?->label() ?? ucfirst($patient->status?->value ?? 'unknown') }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.patients.show', $patient) }}" class="btn btn-outline-primary">{{ __('common.view') }}</a>
                                        @can('patients.edit')
                                            <a href="{{ route('admin.patients.edit', $patient) }}" class="btn btn-outline-secondary">{{ __('common.edit') }}</a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">{{ __('patients.index.empty_filtered') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $patients->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

