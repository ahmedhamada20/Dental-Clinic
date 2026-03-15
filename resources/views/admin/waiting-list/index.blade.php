@extends('admin.layouts.app')

@section('title', __('Waiting List Management'))

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.dashboard.index') }}">{{ __('common.breadcrumb_dashboard') }}</a>
            </li>
            <li class="breadcrumb-item active">{{ __('Waiting List') }}</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-left border-primary">
                <div class="card-body">
                    <div class="text-primary mb-2">
                        <i class="bi bi-hourglass-split fs-3"></i>
                    </div>
                    <h6 class="text-muted mb-1">{{ __('Total Waiting') }}</h6>
                    <h3 class="mb-0">{{ $stats['total_waiting'] ?? 0 }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left border-info">
                <div class="card-body">
                    <div class="text-info mb-2">
                        <i class="bi bi-clock-history fs-3"></i>
                    </div>
                    <h6 class="text-muted mb-1">{{ __('Pending') }}</h6>
                    <h3 class="mb-0">{{ $stats['pending'] ?? 0 }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left border-success">
                <div class="card-body">
                    <div class="text-success mb-2">
                        <i class="bi bi-check-circle fs-3"></i>
                    </div>
                    <h6 class="text-muted mb-1">{{ __('Converted') }}</h6>
                    <h3 class="mb-0">{{ $stats['converted'] ?? 0 }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left border-danger">
                <div class="card-body">
                    <div class="text-danger mb-2">
                        <i class="bi bi-x-circle fs-3"></i>
                    </div>
                    <h6 class="text-muted mb-1">{{ __('Cancelled') }}</h6>
                    <h3 class="mb-0">{{ $stats['cancelled'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Waiting List Table -->
    <div class="card">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>{{ __('Waiting List') }}</h5>
                </div>
                <div class="col-md-4 text-end">
                    @can('appointments.create')
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addWaitingModal">
                            <i class="bi bi-plus-circle"></i> {{ __('Add Request') }}
                        </button>
                    @endcan
                </div>
            </div>
        </div>

        <div class="card-body">
            <!-- Filter Section -->
            <form method="GET" class="row g-2 mb-4">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="{{ __('Search patient...') }}" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">{{ __('All Status') }}</option>
                        <option value="waiting" @selected(request('status') === 'waiting')>{{ __('Pending') }}</option>
                        <option value="notified" @selected(request('status') === 'notified')>{{ __('Notified') }}</option>
                        <option value="booked" @selected(request('status') === 'booked')>{{ __('Converted') }}</option>
                        <option value="expired" @selected(request('status') === 'expired')>{{ __('Expired') }}</option>
                        <option value="cancelled" @selected(request('status') === 'cancelled')>{{ __('Cancelled') }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="specialty_id" class="form-select form-select-sm">
                        <option value="">{{ __('All Specialties') }}</option>
                        @foreach($specialties ?? [] as $specialty)
                            <option value="{{ $specialty->id }}" @selected(request('specialty_id') == $specialty->id)>
                                {{ $specialty->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control form-control-sm" placeholder="{{ __('From') }}" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_to" class="form-control form-control-sm" placeholder="{{ __('To') }}" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                        <i class="bi bi-search"></i> {{ __('common.filter') }}
                    </button>
                    <a href="{{ route('admin.waiting-list.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </form>

            <!-- Waiting List Table -->
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="waitingListTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 100px;">{{ __('Request #') }}</th>
                            <th>{{ __('Patient') }}</th>
                            <th style="width: 100px;">{{ __('Specialty') }}</th>
                            <th style="width: 100px;">{{ __('Date Added') }}</th>
                            <th style="width: 100px;">{{ __('Days Waiting') }}</th>
                            <th style="width: 100px;">{{ __('common.status') }}</th>
                            <th style="width: 200px;">{{ __('common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($waitingListRequests ?? [] as $request)
                            <tr>
                                <td><strong>#{{ $request->id }}</strong></td>
                                <td>
                                    <div>{{ $request->patient?->full_name ?? __('N/A') }}</div>
                                    <small class="text-muted">{{ $request->patient?->phone ?? '' }}</small>
                                </td>
                                <td>{{ $request->service?->category?->medicalSpecialty?->name ?? __('N/A') }}</td>
                                <td>{{ $request->created_at?->format('M d, Y') ?? __('N/A') }}</td>
                                <td>{{ $request->created_at?->diffInDays(now()) ?? 0 }} {{ __('days') }}</td>
                                <td>
                                    <span class="badge bg-{{ match($request->status?->value ?? (string) $request->status) {
                                        'notified' => 'info',
                                        'booked' => 'success',
                                        'expired' => 'dark',
                                        'cancelled' => 'danger',
                                        default => 'warning'
                                    } }}">
                                        {{ ucfirst($request->status?->value ?? (string) $request->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('admin.waiting-list.show', $request) }}" class="btn btn-outline-primary" title="{{ __('common.view') }}">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if(!in_array(($request->status?->value ?? (string) $request->status), ['booked', 'cancelled', 'expired'], true))
                                            <form method="POST" action="{{ route('admin.waiting-list.notify', $request) }}" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-info btn-sm" title="{{ __('Notify') }}" onclick="return confirm('{{ __('Send notification to patient?') }}')">
                                                    <i class="bi bi-bell"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.waiting-list.convert', $request) }}" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-success btn-sm" title="{{ __('Convert to Appointment') }}" onclick="return confirm('{{ __('Convert to appointment?') }}')">
                                                    <i class="bi bi-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('admin.waiting-list.destroy', $request) }}" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="{{ __('common.delete') }}" onclick="return confirm('{{ __('common.confirm_delete') }}')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @isset($waitingListRequests)
                <div class="card-footer">
                    {{ $waitingListRequests->links() }}
                </div>
            @endisset
        </div>
    </div>
</div>

<!-- Add Waiting List Modal -->
<div class="modal fade" id="addWaitingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Add to Waiting List') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.waiting-list.store') }}" novalidate>
                @csrf
                <div class="modal-body">
                    <!-- Patient Selection -->
                    <div class="mb-3">
                        <label for="patient_id" class="form-label">{{ __('Patient') }} <span class="text-danger">*</span></label>
                        <select name="patient_id" id="patient_id" class="form-select @error('patient_id') is-invalid @enderror" required>
                            <option value="">{{ __('Select a patient...') }}</option>
                            @foreach($patients ?? [] as $patient)
                                <option value="{{ $patient->id }}" @selected(old('patient_id') == $patient->id)>
                                    {{ $patient->full_name }} ({{ $patient->phone }})
                                </option>
                            @endforeach
                        </select>
                        @error('patient_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Specialty Selection -->
                    <div class="mb-3">
                        <label for="specialty_id" class="form-label">{{ __('Specialty') }} <span class="text-danger">*</span></label>
                        <select name="specialty_id" id="specialty_id" class="form-select @error('specialty_id') is-invalid @enderror" required>
                            <option value="">{{ __('Select a specialty...') }}</option>
                            @foreach($specialties ?? [] as $specialty)
                                <option value="{{ $specialty->id }}" @selected(old('specialty_id') == $specialty->id)>
                                    {{ $specialty->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('specialty_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Preferred Date -->
                    <div class="mb-3">
                        <label for="preferred_date" class="form-label">{{ __('Preferred Date') }}</label>
                        <input type="date" name="preferred_date" id="preferred_date" class="form-control @error('preferred_date') is-invalid @enderror" value="{{ old('preferred_date') }}">
                        @error('preferred_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Add to Waiting List') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTables
    const table = document.getElementById('waitingListTable');
    if (table && !$.fn.DataTable.isDataTable(table)) {
        $(table).DataTable({
            pageLength: 15,
            responsive: true,
            order: [[4, 'desc']],
            language: {
                emptyTable: '{{ __('No waiting list requests found') }}'
            },
            columnDefs: [
                { orderable: false, targets: -1 }
            ]
        });
    }

    // Form validation
    const forms = document.querySelectorAll('form[novalidate]');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!this.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            this.classList.add('was-validated');
        });
    });
});
</script>
@endpush
