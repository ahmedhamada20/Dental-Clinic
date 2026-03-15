@extends('admin.layouts.app')

@section('title', __('appointments.title'))

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">{{ __('appointments.title') }}</h1>
        <div class="d-flex gap-2">
            @can('appointments.create')
                <a href="{{ route('admin.appointments.create') }}" class="btn btn-primary btn-sm">{{ __('appointments.new') }}</a>
            @endcan
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="GET" class="card card-body mb-3">
        <div class="row g-2">
            <div class="col-md-3">
                <label class="form-label">{{ __('common.date') }}</label>
                <input type="date" name="date" class="form-control" value="{{ request('date') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('common.status') }}</label>
                <select name="status" class="form-select">
                    <option value="">{{ __('common.all') }}</option>
                    @foreach ($statuses as $status)
                        <option value="{{ is_object($status) ? $status->value : $status }}" @selected(request('status') === (is_object($status) ? $status->value : $status))>
                            {{ is_object($status) ? $status->label() : str_replace('_', ' ', ucfirst($status)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('appointments.filters.specialty') }}</label>
                <select name="specialty_id" class="form-select">
                    <option value="">{{ __('common.all') }}</option>
                    @foreach ($specialties as $specialty)
                        <option value="{{ $specialty->id }}" @selected((string) request('specialty_id') === (string) $specialty->id)>
                            {{ $specialty->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-outline-primary w-100">{{ __('common.filter') }}</button>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('appointments.columns.patient') }}</th>
                        <th>{{ __('appointments.columns.specialty') }}</th>
                        <th>{{ __('appointments.columns.doctor') }}</th>
                        <th>{{ __('appointments.columns.service') }}</th>
                        <th>{{ __('common.date') }}</th>
                        <th>{{ __('appointments.columns.time') }}</th>
                        <th>{{ __('common.status') }}</th>
                        <th class="text-end">{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($appointments as $appointment)
                        <tr>
                            <td>{{ $appointment->appointment_no ?? $appointment->id }}</td>
                            <td>{{ optional($appointment->patient)->full_name ?? __('common.not_available') }}</td>
                            <td>{{ $appointment->specialty?->name ?? $appointment->service?->category?->medicalSpecialty?->name ?? __('common.not_available') }}</td>
                            <td>{{ $appointment->doctor?->display_name ?? __('common.not_available') }}</td>
                            <td>{{ $appointment->service?->name_en ?: ($appointment->service?->name_ar ?? __('common.not_available')) }}</td>
                            <td>{{ optional($appointment->appointment_date)->format('Y-m-d') }}</td>
                            <td>{{ $appointment->appointment_time ?? '-' }}</td>
                            <td>{{ is_object($appointment->status) ? $appointment->status->label() : str_replace('_', ' ', ucfirst((string) $appointment->status)) }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.appointments.show', $appointment) }}" class="btn btn-sm btn-outline-info">{{ __('common.view') }}</a>
                                @can('appointments.edit')
                                    <a href="{{ route('admin.appointments.edit', $appointment) }}" class="btn btn-sm btn-outline-primary">{{ __('common.edit') }}</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center py-3">{{ __('appointments.empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">{{ $appointments->links() }}</div>
    </div>
</div>
@endsection

