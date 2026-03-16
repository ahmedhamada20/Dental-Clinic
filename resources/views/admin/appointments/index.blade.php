@extends('admin.layouts.app')

@section('title', __('appointments.title'))

@push('styles')
<style>
    .appointments-calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 0.75rem;
    }

    .appointments-calendar-day {
        min-height: 180px;
        border: 1px solid var(--bs-border-color);
        border-radius: 0.75rem;
        background: var(--bs-body-bg);
        padding: 0.75rem;
    }

    .appointments-calendar-day.is-outside {
        opacity: 0.6;
        background: #f8f9fa;
    }

    .appointments-calendar-day.is-today {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 1px rgba(var(--bs-primary-rgb), 0.15);
    }

    .appointments-calendar-event {
        border: 1px solid rgba(var(--bs-primary-rgb), 0.2);
        border-radius: 0.5rem;
        padding: 0.5rem;
        background: rgba(var(--bs-primary-rgb), 0.05);
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    @php
        $today = now()->toDateString();
        $weekDays = ['Sat', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
        $calendarCursor = $calendarStart->copy();
        $monthFilter = request()->input('month', $calendarDate->format('Y-m'));
        $prevMonth = $calendarDate->copy()->subMonth()->format('Y-m');
        $nextMonth = $calendarDate->copy()->addMonth()->format('Y-m');
        $prevMonthQuery = array_merge(request()->except(['month', 'page']), ['month' => $prevMonth]);
        $nextMonthQuery = array_merge(request()->except(['month', 'page']), ['month' => $nextMonth]);
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">{{ __('appointments.title') }}</h1>
        <div class="d-flex gap-2 align-items-center">
            <a href="{{ route('admin.appointments.index', $prevMonthQuery) }}" class="btn btn-outline-secondary btn-sm">&larr;</a>
            <span class="badge text-bg-light fs-6">{{ $calendarDate->format('F Y') }}</span>
            <a href="{{ route('admin.appointments.index', $nextMonthQuery) }}" class="btn btn-outline-secondary btn-sm">&rarr;</a>
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
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">{{ __('appointments.columns.patient') }}</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="{{ __('common.search') }} {{ __('appointments.columns.patient') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Month</label>
                <input type="month" name="month" class="form-control" value="{{ $monthFilter }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">{{ __('common.date') }}</label>
                <input type="date" name="date" class="form-control" value="{{ request('date') }}">
            </div>
            <div class="col-md-2">
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
            <div class="col-md-2">
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
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-outline-primary w-100">{{ __('common.filter') }}</button>
                <a href="{{ route('admin.appointments.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="card-body">
            @if ($appointments->isEmpty())
                <p class="text-muted mb-0">{{ __('appointments.empty') }}</p>
            @else
                <div class="appointments-calendar-grid mb-3">
                    @foreach ($weekDays as $weekDay)
                        <div class="text-center fw-semibold text-muted small">{{ $weekDay }}</div>
                    @endforeach
                </div>

                <div class="appointments-calendar-grid">
                    @while ($calendarCursor->lte($calendarEnd))
                        @php
                            $dateKey = $calendarCursor->toDateString();
                            $dayAppointments = $appointmentsByDate->get($dateKey, collect());
                            $isOutsideMonth = $calendarCursor->month !== $calendarDate->month;
                            $isToday = $dateKey === $today;
                        @endphp

                        <div @class(['appointments-calendar-day', 'is-outside' => $isOutsideMonth, 'is-today' => $isToday])>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-semibold">{{ $calendarCursor->format('d') }}</span>
                                @if ($dayAppointments->isNotEmpty())
                                    <span class="badge text-bg-primary">{{ $dayAppointments->count() }}</span>
                                @endif
                            </div>

                            <div class="d-flex flex-column gap-2">
                                @foreach ($dayAppointments as $appointment)
                                    @php
                                        $currentStatus = $appointment->status?->value ?? $appointment->status;
                                        $isConfirmedLocked = $currentStatus === \App\Enums\AppointmentStatus::CONFIRMED->value;
                                        $quickStatuses = [
                                            \App\Enums\AppointmentStatus::CONFIRMED,
                                            \App\Enums\AppointmentStatus::CHECKED_IN,
                                            \App\Enums\AppointmentStatus::NO_SHOW,
                                            \App\Enums\AppointmentStatus::COMPLETED,
                                        ];
                                        $quickStatusLabels = [
                                            \App\Enums\AppointmentStatus::CONFIRMED->value => ' تم التأكيد',
                                            \App\Enums\AppointmentStatus::CHECKED_IN->value => ' تم تسجيل الحضور',
                                            \App\Enums\AppointmentStatus::NO_SHOW->value => 'لم يحضر',
                                            \App\Enums\AppointmentStatus::COMPLETED->value => ' مكتمل',
                                        ];
                                    @endphp
                                    <div class="appointments-calendar-event small">
                                        <div class="fw-semibold">{{ $appointment->start_time ? \Illuminate\Support\Str::substr((string) $appointment->start_time, 0, 5) : '--:--' }}</div>
                                        <div>{{ $appointment->patient?->full_name ?? __('common.not_available') }}</div>
                                        <div class="text-muted">{{ $appointment->doctor?->display_name ?? __('common.not_available') }}</div>
                                        <div class="mt-1">
                                            <span class="badge text-bg-secondary">
                                                {{ is_object($appointment->status) ? $appointment->status->label() : str_replace('_', ' ', ucfirst((string) $appointment->status)) }}
                                            </span>
                                        </div>
                                        <div class="mt-1 d-flex flex-wrap gap-1">
                                            <a href="{{ route('admin.appointments.show', $appointment) }}" class="btn btn-sm btn-outline-info">{{ __('common.view') }}</a>
                                            @if ($appointment->patient_id)
                                                <a href="{{ route('admin.patients.show', $appointment->patient_id) }}" class="btn btn-sm btn-outline-primary">{{ __('appointments.columns.patient') }}</a>
                                            @endif
                                            @if ($appointment->visit)
                                                <a href="{{ route('admin.visits.show', $appointment->visit) }}" class="btn btn-sm btn-outline-success">Visit</a>
                                            @endif
                                        </div>

                                        @can('appointments.edit')
                                            <form method="POST" action="{{ route('admin.appointments.status.update', $appointment) }}" class="mt-2">
                                                @csrf
                                                <div class="input-group input-group-sm">
                                                    <select class="form-select" name="status" @disabled($isConfirmedLocked)>
                                                        @foreach ($quickStatuses as $quickStatus)
                                                            <option value="{{ $quickStatus->value }}" @selected($currentStatus === $quickStatus->value)>
                                                                {{ $quickStatusLabels[$quickStatus->value] ?? $quickStatus->label() }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <button class="btn btn-outline-info" type="submit" @disabled($isConfirmedLocked)>{{__('common.edit')}}</button>
                                                </div>
                                                @if ($isConfirmedLocked)
                                                    <div class="text-muted mt-1">Confirmed / تم التأكيد - Locked</div>
                                                @endif
                                            </form>
                                        @endcan
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        @php($calendarCursor->addDay())
                    @endwhile
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

