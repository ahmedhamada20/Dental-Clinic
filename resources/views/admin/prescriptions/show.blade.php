@extends('admin.layouts.app')

@section('title', __('admin.prescriptions.details'))

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="h4 mb-2">{{ __('admin.prescriptions.details') }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.prescriptions.index') }}">{{ __('admin.prescriptions.title') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('admin.prescriptions.view_prescription') }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            @can('prescriptions.edit')
                <a href="{{ route('admin.prescriptions.edit', $prescription) }}" class="btn btn-outline-warning btn-sm">
                    <i class="bi bi-pencil"></i> {{ __('admin.prescriptions.edit_prescription') }}
                </a>
            @endcan
            <a href="{{ route('admin.prescriptions.print', $prescription) }}" class="btn btn-outline-primary btn-sm" target="_blank">
                <i class="bi bi-printer"></i> {{ __('Print Prescription') }}
            </a>
            <a href="{{ route('admin.prescriptions.index') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> {{ __('admin.back') }}
            </a>
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
        <!-- Prescription Details -->
        <div class="col-lg-8">
            <!-- Header Card -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">{{ __('admin.prescriptions.issued_date') }}</h6>
                            <h5>{{ optional($prescription->issued_at)->format('Y-m-d H:i') ?? 'N/A' }}</h5>
                        </div>
                        <div class="col-md-6 text-end">
                            <h6 class="text-muted mb-1">{{ __('Status') }}</h6>
                            <h5><span class="badge text-bg-success">{{ __('Active') }}</span></h5>
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
                            <h6 class="text-muted">{{ __('admin.prescriptions.patient_name') }}</h6>
                            @if ($prescription->patient)
                                <p class="mb-0">
                                    <a href="{{ route('admin.patients.show', $prescription->patient) }}">
                                        <strong>{{ $prescription->patient->full_name }}</strong>
                                    </a><br>
                                    <small class="text-muted">ID: {{ $prescription->patient->id }}</small>
                                </p>
                            @else
                                <p class="mb-0">N/A</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">{{ __('Contact Information') }}</h6>
                            @if ($prescription->patient)
                                <p class="mb-0">
                                    {{ __('Phone') }}: {{ $prescription->patient->phone ?? 'N/A' }}<br>
                                    {{ __('Email') }}: {{ $prescription->patient->email ?? 'N/A' }}
                                </p>
                            @else
                                <p class="mb-0">N/A</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Doctor Information -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">{{ __('Doctor Information') }}</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">{{ __('admin.prescriptions.doctor_name') }}</h6>
                            @if ($prescription->doctor)
                                <p class="mb-0">
                                    <strong>{{ $prescription->doctor->display_name ?? $prescription->doctor->full_name ?? 'N/A' }}</strong><br>
                                    <small class="text-muted">ID: {{ $prescription->doctor->id }}</small>
                                </p>
                            @else
                                <p class="mb-0">N/A</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">{{ __('admin.prescriptions.visit_no') }}</h6>
                            @if ($prescription->visit)
                                <p class="mb-0">
                                    <a href="{{ route('admin.visits.show', $prescription->visit) }}">
                                        <strong>{{ $prescription->visit->visit_no }}</strong>
                                    </a><br>
                                    <small class="text-muted">{{ optional($prescription->visit->visit_date)->format('Y-m-d') }}</small>
                                </p>
                            @else
                                <p class="mb-0">N/A</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Medicines -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">{{ __('admin.prescriptions.medicines') }}</h6>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('admin.prescriptions.medicine_name') }}</th>
                                <th>{{ __('admin.prescriptions.dosage') }}</th>
                                <th>{{ __('admin.prescriptions.frequency') }}</th>
                                <th>{{ __('admin.prescriptions.dose_duration') }}</th>
                                <th>{{ __('admin.prescriptions.treatment_duration') }}</th>
                                <th>{{ __('admin.prescriptions.instructions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($prescription->items as $item)
                                <tr>
                                    <td>{{ $item->medicine_name }}</td>
                                    <td>{{ $item->dosage }}</td>
                                    <td>{{ $item->frequency }}</td>
                                    <td>{{ $item->dose_duration ?? '-' }}</td>
                                    <td>{{ $item->treatment_duration ?? $item->duration ?? '-' }}</td>
                                    <td>
                                        <small>{{ Str::limit($item->instructions, 50) }}</small>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">
                                        {{ __('No medicines prescribed') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Prescription Notes -->
            @if ($prescription->notes)
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">{{ __('admin.prescriptions.notes') }}</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $prescription->notes }}</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header">
                    <h6 class="mb-0">{{ __('admin.prescriptions.summary') }}</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-muted">{{ __('admin.prescriptions.medicines') }}</h6>
                        <h5 class="badge text-bg-info">
                            {{ $prescription->items_count ?? $prescription->items->count() }}
                        </h5>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-muted">{{ __('admin.prescriptions.issued_date') }}</h6>
                        <p class="mb-0">{{ optional($prescription->issued_at)->format('Y-m-d H:i') ?? 'N/A' }}</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-muted">{{ __('Status') }}</h6>
                        <p class="mb-0"><span class="badge text-bg-success">{{ __('Active') }}</span></p>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-grid gap-2">
                        @if ($prescription->patient)
                            <a href="{{ route('admin.patients.show', $prescription->patient) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-person"></i> {{ __('View Patient') }}
                            </a>
                        @endif
                        @if ($prescription->visit)
                            <a href="{{ route('admin.visits.show', $prescription->visit) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-calendar"></i> {{ __('View Visit') }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

