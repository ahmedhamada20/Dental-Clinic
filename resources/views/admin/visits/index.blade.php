@extends('admin.layouts.app')

@section('title', __('visits.title'))

@section('content')
@php
    $user = auth()->user();
    $canCreate = $user?->can('visits.create') && \Illuminate\Support\Facades\Route::has('admin.visits.create');
    $canEdit   = $user?->can('visits.edit')   && \Illuminate\Support\Facades\Route::has('admin.visits.edit');
    $canDelete = $user?->can('visits.edit')   && \Illuminate\Support\Facades\Route::has('admin.visits.destroy');
@endphp

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">{{ __('visits.title') }}</h1>
            <p class="text-muted mb-0">{{ __('visits.subtitle') }}</p>
        </div>
        @if ($canCreate)
            <a href="{{ route('admin.visits.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>{{ __('visits.create') }}
            </a>
        @else
            <button type="button" class="btn btn-primary" disabled>
                <i class="bi bi-plus-circle me-1"></i>{{ __('visits.create') }}
            </button>
        @endif
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('common.close') }}"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('common.close') }}"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>{{ __('admin.validation_errors') }}</strong>
            <ul class="mb-0 mt-2 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('common.close') }}"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">{{ __('common.search') }}</label>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="{{ __('visits.visit_no') }}, {{ __('visits.patient') }}, {{ __('visits.doctor') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ __('common.status') }}</label>
                    <select name="status" class="form-select">
                        <option value="">{{ __('common.all') }}</option>
                        <option value="scheduled"   @selected(request('status') === 'scheduled')>{{ __('appointments.status.scheduled') }}</option>
                        <option value="in_progress" @selected(request('status') === 'in_progress')>{{ __('appointments.status.in_progress') }}</option>
                        <option value="completed"   @selected(request('status') === 'completed')>{{ __('appointments.status.completed') }}</option>
                        <option value="cancelled"   @selected(request('status') === 'cancelled')>{{ __('appointments.status.cancelled') }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ __('common.date') }}</label>
                    <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ __('visits.from_date') }}</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ __('visits.to_date') }}</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-1 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
            <div class="mt-2">
                <a href="{{ route('admin.visits.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('common.reset') }}</a>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="visitsTable">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('visits.visit_no') }}</th>
                        <th>{{ __('common.date') }}</th>
                        <th>{{ __('visits.patient') }}</th>
                        <th>{{ __('visits.doctor') }}</th>
                        <th>{{ __('common.status') }}</th>
                        <th>{{ __('visits.complaints') }}</th>
                        <th>{{ __('visits.diagnosis') }}</th>
                        <th class="text-end">{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($visits as $visit)
                        <tr>
                            <td class="fw-semibold">{{ $visit->visit_no }}</td>
                            <td>{{ $visit->visit_date?->format('Y-m-d') }}</td>
                            <td>{{ $visit->patient?->full_name ?? $visit->patient?->displayName ?? __('common.not_available') }}</td>
                            <td>{{ $visit->doctor?->display_name ?? $visit->doctor?->full_name ?? __('common.not_available') }}</td>
                            <td>
                                <span class="badge bg-{{ match($visit->status?->value ?? (string) $visit->status) {
                                    'scheduled'   => 'info',
                                    'in_progress' => 'warning',
                                    'completed'   => 'success',
                                    'cancelled'   => 'danger',
                                    default       => 'secondary'
                                } }}">
                                    {{ $visit->status?->label() ?? ucfirst(str_replace('_', ' ', $visit->status?->value ?? (string) $visit->status)) }}
                                </span>
                            </td>
                            <td>{{ \Illuminate\Support\Str::limit($visit->chief_complaint ?? __('common.not_available'), 35) }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($visit->diagnosis ?? __('common.not_available'), 35) }}</td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.visits.show', $visit) }}" class="btn btn-outline-primary">{{ __('common.view') }}</a>
                                    @if ($canEdit)
                                        <a href="{{ route('admin.visits.edit', $visit) }}" class="btn btn-outline-secondary">{{ __('common.edit') }}</a>
                                    @else
                                        <button type="button" class="btn btn-outline-secondary" disabled>{{ __('common.edit') }}</button>
                                    @endif
                                    @if ($canDelete)
                                        <form action="{{ route('admin.visits.destroy', $visit) }}" method="POST" onsubmit="return confirm('{{ __('visits.confirm_delete') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger">{{ __('common.delete') }}</button>
                                        </form>
                                    @else
                                        <button type="button" class="btn btn-outline-danger" disabled>{{ __('common.delete') }}</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const table = document.getElementById('visitsTable');
    if (table && window.$ && $.fn.DataTable && !$.fn.DataTable.isDataTable(table)) {
        $(table).DataTable({
            pageLength: 15,
            responsive: true,
            order: [[1, 'desc']],
            language: {
                emptyTable: '{{ __('visits.no_visits_table') }}'
            },
            columnDefs: [{ orderable: false, targets: -1 }]
        });
    }
});
</script>
@endpush
