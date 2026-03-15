@extends('admin.layouts.app')

@section('title', __('appointments.pages.edit.title', ['number' => $appointment->appointment_no ?? $appointment->id]))

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-1">{{ __('appointments.pages.edit.title', ['number' => $appointment->appointment_no ?? $appointment->id]) }}</h1>
            <p class="text-muted mb-0">{{ __('appointments.pages.edit.subtitle') }}</p>
        </div>
        <a href="{{ route('admin.appointments.show', $appointment) }}" class="btn btn-outline-secondary">{{ __('appointments.pages.edit.back') }}</a>
    </div>

    <form method="POST" action="{{ route('admin.appointments.update', $appointment) }}">
        @csrf
        @method('PUT')
        @include('admin.appointments._form', ['appointment' => $appointment])
    </form>
</div>
@endsection

