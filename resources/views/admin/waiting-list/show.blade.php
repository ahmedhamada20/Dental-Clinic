@extends('admin.layouts.app')

@section('title', __('waiting_list.title'))

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.dashboard.index') }}">{{ __('common.breadcrumb_dashboard') }}</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('admin.waiting-list.index') }}">{{ __('waiting_list.list') }}</a>
            </li>
            <li class="breadcrumb-item active">{{ __('waiting_list.request') }}{{ $waitingListRequest->id }}</li>
        </ol>
    </nav>
@endsection

@section('content')
@php
    $statusValue = $waitingListRequest->status?->value ?? (string) $waitingListRequest->status;
@endphp
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="mb-0">
                                {{ __('waiting_list.request') }}{{ $waitingListRequest->id }}
                                <span class="badge bg-{{ match($statusValue) {
                                    'notified' => 'info',
                                    'booked' => 'success',
                                    'expired' => 'dark',
                                    'cancelled' => 'danger',
                                    default => 'warning'
                                } }} ms-2">
                                    {{ ucfirst($statusValue) }}
                                </span>
                            </h5>
                        </div>
                        <div class="col-md-4 text-end">
                            @if(!in_array($statusValue, ['booked', 'cancelled', 'expired'], true))
                                <div class="btn-group btn-group-sm" role="group">
                                    @can('appointments.edit')
                                        <form method="POST" action="{{ route('admin.waiting-list.notify', $waitingListRequest) }}" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-info" title="{{ __('waiting_list.send_notification') }}" onclick="return confirm('{{ __('waiting_list.send_notification_confirm') }}')">
                                                <i class="bi bi-bell"></i> {{ __('waiting_list.notify') }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.waiting-list.convert', $waitingListRequest) }}" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success" title="{{ __('waiting_list.convert_to_appointment') }}" onclick="return confirm('{{ __('waiting_list.convert_confirm') }}')">
                                                <i class="bi bi-check"></i> {{ __('waiting_list.convert') }}
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">{{ __('waiting_list.patient_information') }}</h6>
                            <dl class="row small mb-0">
                                <dt class="col-sm-5">{{ __('waiting_list.name') }}</dt>
                                <dd class="col-sm-7">
                                    <a href="{{ route('admin.patients.show', $waitingListRequest->patient) }}">
                                        {{ $waitingListRequest->patient?->full_name ?? __('common.not_available') }}
                                    </a>
                                </dd>

                                <dt class="col-sm-5">{{ __('waiting_list.phone') }}</dt>
                                <dd class="col-sm-7">{{ $waitingListRequest->patient?->phone ?? __('common.not_available') }}</dd>

                                <dt class="col-sm-5">{{ __('waiting_list.email') }}</dt>
                                <dd class="col-sm-7">{{ $waitingListRequest->patient?->email ?? __('common.not_available') }}</dd>

                                <dt class="col-sm-5">{{ __('waiting_list.status') }}</dt>
                                <dd class="col-sm-7">
                                    @php($patientStatus = $waitingListRequest->patient?->status?->value ?? (string) $waitingListRequest->patient?->status)
                                    <span class="badge bg-{{ $patientStatus === 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($patientStatus ?: 'unknown') }}
                                    </span>
                                </dd>
                            </dl>
                        </div>

                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">{{ __('waiting_list.request_information') }}</h6>
                            <dl class="row small mb-0">
                                <dt class="col-sm-5">{{ __('waiting_list.specialty') }}</dt>
                                <dd class="col-sm-7">{{ $waitingListRequest->service?->category?->medicalSpecialty?->name ?? __('common.not_available') }}</dd>

                                <dt class="col-sm-5">{{ __('waiting_list.date_added') }}</dt>
                                <dd class="col-sm-7">{{ $waitingListRequest->created_at?->format('M d, Y H:i') ?? __('common.not_available') }}</dd>

                                <dt class="col-sm-5">{{ __('waiting_list.days_waiting') }}</dt>
                                <dd class="col-sm-7"><strong>{{ $waitingListRequest->created_at?->diffInDays(now()) ?? 0 }}</strong></dd>

                                <dt class="col-sm-5">{{ __('waiting_list.preferred_date') }}</dt>
                                <dd class="col-sm-7">{{ $waitingListRequest->preferred_date?->format('M d, Y') ?? __('waiting_list.not_specified') }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>{{ __('waiting_list.activity_timeline') }}</h6>
                </div>
                <div class="card-body small">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-date">{{ $waitingListRequest->created_at?->format('M d, Y H:i') }}</div>
                            <div class="timeline-content">
                                <strong>{{ __('waiting_list.added_to_waiting_list') }}</strong>
                            </div>
                        </div>
                        @if($waitingListRequest->notified_at)
                            <div class="timeline-item">
                                <div class="timeline-date">{{ $waitingListRequest->notified_at?->format('M d, Y H:i') }}</div>
                                <div class="timeline-content">
                                    <strong>{{ __('waiting_list.notified') }}</strong>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-queue-front me-2"></i>{{ __('waiting_list.queue_position') }}</h6>
                </div>
                <div class="card-body text-center">
                    <h2 class="text-primary mb-2">#{{ $queuePosition ?? 1 }}</h2>
                    <p class="text-muted small mb-0">{{ __('waiting_list.current_position_in_queue', ['total' => $queueTotal ?? 1]) }}</p>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>{{ __('waiting_list.status_information') }}</h6>
                </div>
                <div class="card-body small">
                    <div class="mb-3">
                        <strong>{{ __('waiting_list.current_status') }}</strong><br>
                        <span class="badge mt-2 bg-{{ match($statusValue) {
                            'notified' => 'info',
                            'booked' => 'success',
                            'expired' => 'dark',
                            'cancelled' => 'danger',
                            default => 'warning'
                        } }}">
                            {{ ucfirst($statusValue) }}
                        </span>
                    </div>

                    <hr class="my-2">

                    @if($statusValue === 'pending')
                        <p class="text-muted mb-0">{{ __('waiting_list.pending_message') }}</p>
                    @elseif($statusValue === 'notified')
                        <p class="text-muted mb-0">{{ __('waiting_list.notified_message') }}</p>
                    @elseif($statusValue === 'booked')
                        <p class="text-muted mb-0">{{ __('waiting_list.fulfilled_message') }}</p>
                        @if($waitingListRequest->bookedAppointment)
                            <p class="mt-2 mb-0">
                                <a href="{{ route('admin.appointments.show', $waitingListRequest->bookedAppointment) }}">
                                    {{ __('waiting_list.view_appointment') }} ->
                                </a>
                            </p>
                        @endif
                    @elseif($statusValue === 'expired')
                        <p class="text-muted mb-0">{{ __('Request has expired and is no longer claimable.') }}</p>
                    @elseif($statusValue === 'cancelled')
                        <p class="text-muted mb-0">{{ __('waiting_list.cancelled_message') }}</p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-sliders me-2"></i>{{ __('common.actions') }}</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if(!in_array($statusValue, ['booked', 'cancelled', 'expired'], true))
                            @can('appointments.edit')
                                <form method="POST" action="{{ route('admin.waiting-list.notify', $waitingListRequest) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-info btn-sm" onclick="return confirm('{{ __('waiting_list.send_notification_confirm') }}')">
                                        <i class="bi bi-bell"></i> {{ __('waiting_list.send_notification') }}
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.waiting-list.convert', $waitingListRequest) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('{{ __('waiting_list.convert_confirm') }}')">
                                        <i class="bi bi-check-circle"></i> {{ __('waiting_list.convert_to_appointment') }}
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.waiting-list.cancel', $waitingListRequest) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('{{ __('waiting_list.cancel_confirm') }}')">
                                        <i class="bi bi-x-circle"></i> {{ __('waiting_list.cancel_request') }}
                                    </button>
                                </form>
                            @endcan
                        @endif

                        @can('appointments.edit')
                            <form method="POST" action="{{ route('admin.waiting-list.destroy', $waitingListRequest) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('{{ __('waiting_list.delete_request') }}')">
                                    <i class="bi bi-trash"></i> {{ __('common.delete') }}
                                </button>
                            </form>
                        @endcan

                        <a href="{{ route('admin.waiting-list.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> {{ __('waiting_list.back_to_list') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
}

.timeline-item {
    padding: 15px;
    border-left: 3px solid #667eea;
    padding-left: 20px;
    margin-left: 10px;
}

.timeline-date {
    font-weight: 600;
    color: #667eea;
    font-size: 0.85rem;
}

.timeline-content {
    margin-top: 5px;
}
</style>
@endsection

