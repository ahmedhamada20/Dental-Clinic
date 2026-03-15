@extends('admin.layouts.app')

@section('title', __('admin.sidebar.appointments'))

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">{{ __('admin.sidebar.appointments') }} - Timeline</h1>
        <a href="{{ route('admin.appointments.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('admin.back') }}</a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('admin.billing.patient') }}</th>
                        <th>{{ __('admin.sidebar.specialties') }}</th>
                        <th>{{ __('admin.date') }}</th>
                        <th>{{ __('admin.status') }}</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($appointments as $appointment)
                        <tr>
                            <td>{{ $appointment->appointment_no ?? $appointment->id }}</td>
                            <td>{{ $appointment->patient?->full_name ?? 'N/A' }}</td>
                            <td>{{ $appointment->specialty?->name ?? 'N/A' }}</td>
                            <td>{{ optional($appointment->appointment_date)->format('Y-m-d') }}</td>
                            <td>{{ is_object($appointment->status) ? $appointment->status->label() : (string) $appointment->status }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.appointments.show', $appointment) }}" class="btn btn-sm btn-outline-primary">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-3 text-muted">No appointments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">{{ $appointments->links() }}</div>
    </div>
</div>
@endsection

