@extends('admin.layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-1">Appointment #{{ $appointment->appointment_no ?? $appointment->id }}</h1>
            <p class="text-muted mb-0">Review the linked patient, specialty, doctor, service, and schedule details.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.appointments.edit', $appointment) }}" class="btn btn-primary">Edit</a>
            <a href="{{ route('admin.appointments.index') }}" class="btn btn-outline-secondary">Back to appointments</a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="text-muted small">Patient</div>
                            <div>{{ $appointment->patient?->full_name ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Status</div>
                            <div>{{ is_object($appointment->status) ? $appointment->status->label() : ucfirst((string) $appointment->status) }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Specialty</div>
                            <div>{{ $appointment->specialty?->name ?? $appointment->service?->category?->medicalSpecialty?->name ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Doctor</div>
                            <div>{{ $appointment->doctor?->display_name ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Service</div>
                            <div>{{ $appointment->service?->name_en ?: ($appointment->service?->name_ar ?? 'N/A') }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted small">Date</div>
                            <div>{{ optional($appointment->appointment_date)->format('Y-m-d') ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted small">Time</div>
                            <div>{{ $appointment->appointment_time ?? 'N/A' }}</div>
                        </div>
                        <div class="col-12">
                            <div class="text-muted small">Notes</div>
                            <div>{{ $appointment->notes ?: 'No notes added.' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h2 class="h6">Booking flow</h2>
                    <ol class="mb-0 ps-3">
                        <li>Select specialty</li>
                        <li>Select doctor</li>
                        <li>Select service</li>
                        <li>Select date/time</li>
                        <li>Confirm booking</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

