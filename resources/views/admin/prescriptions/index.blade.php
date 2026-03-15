@extends('admin.layouts.app')

@section('title', __('admin.prescriptions.title'))

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-2">{{ __('admin.prescriptions.list') }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">{{ __('admin.Dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('admin.prescriptions.title') }}</li>
                </ol>
            </nav>
        </div>
                @can('prescriptions.create')
                    <a href="{{ route('admin.prescriptions.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle"></i> {{ __('admin.prescriptions.create_prescription') }}
                    </a>
                @endcan
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

    <!-- Prescriptions Table -->
    <div class="card">
        <!-- Filter Section -->
        <div class="card-header">
            <form method="GET" class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="{{ __('Search patient or notes...') }}" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date" class="form-control form-control-sm"
                           placeholder="{{ __('admin.date') }}" value="{{ request('date') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="from_date" class="form-control form-control-sm"
                           placeholder="{{ __('From Date') }}" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="to_date" class="form-control form-control-sm"
                           placeholder="{{ __('To Date') }}" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-3">
                    <div class="d-grid gap-2" style="grid-template-columns: 1fr 1fr;">
                        <button type="submit" class="btn btn-sm btn-primary">{{ __('admin.filter') }}</button>
                        <a href="{{ route('admin.prescriptions.index') }}" class="btn btn-sm btn-secondary">{{ __('admin.reset') }}</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('admin.prescriptions.issued_date') }}</th>
                        <th>{{ __('admin.prescriptions.patient_name') }}</th>
                        <th>{{ __('admin.prescriptions.doctor_name') }}</th>
                        <th>{{ __('admin.prescriptions.visit_no') }}</th>
                        <th>{{ __('admin.prescriptions.medicines') }}</th>
                        <th>{{ __('admin.prescriptions.notes') }}</th>
                        <th class="text-end">{{ __('admin.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($prescriptions as $prescription)
                        <tr>
                            <td>{{ optional($prescription->issued_at)->format('Y-m-d') }}</td>
                            <td>
                                @if ($prescription->patient)
                                    <a href="{{ route('admin.patients.show', $prescription->patient) }}">
                                        {{ $prescription->patient->full_name }}
                                    </a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if ($prescription->doctor)
                                    {{ $prescription->doctor->display_name ?? $prescription->doctor->name }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if ($prescription->visit)
                                    <a href="{{ route('admin.visits.show', $prescription->visit) }}">
                                        {{ $prescription->visit->visit_no }}
                                    </a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge text-bg-info">
                                    {{ $prescription->items_count ?? $prescription->items->count() }} {{ __('Medicines') }}
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ Str::limit($prescription->notes, 50) }}
                                </small>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm" role="group">
                                    @can('prescriptions.view')
                                        <a href="{{ route('admin.prescriptions.show', $prescription) }}"
                                           class="btn btn-outline-primary" title="{{ __('admin.prescriptions.view_prescription') }}">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    @endcan
                                    @can('prescriptions.edit')
                                        <a href="{{ route('admin.prescriptions.edit', $prescription) }}"
                                           class="btn btn-outline-warning" title="{{ __('admin.prescriptions.edit_prescription') }}">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox"></i> {{ __('admin.prescriptions.no_prescriptions') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($prescriptions->hasPages())
            <div class="card-footer">
                {{ $prescriptions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

