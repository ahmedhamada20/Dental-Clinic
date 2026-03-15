@extends('admin.layouts.app')

@section('title', __('admin.sidebar.appointments'))

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">{{ __('admin.sidebar.appointments') }} - {{ $calendarDate->format('F Y') }}</h1>
        <a href="{{ route('admin.appointments.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('admin.back') }}</a>
    </div>

    <div class="card">
        <div class="card-body">
            @forelse ($appointmentsByDate as $date => $items)
                <div class="mb-3">
                    <h6 class="mb-2">{{ \Carbon\Carbon::parse($date)->format('Y-m-d') }}</h6>
                    <ul class="mb-0">
                        @foreach ($items as $appointment)
                            <li>
                                {{ $appointment->appointment_no ?? $appointment->id }} -
                                {{ $appointment->patient?->full_name ?? 'N/A' }}
                                ({{ $appointment->start_time ?? '-' }})
                            </li>
                        @endforeach
                    </ul>
                </div>
            @empty
                <p class="text-muted mb-0">No appointments found for this month.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

