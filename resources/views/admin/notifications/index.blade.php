@extends('admin.layouts.app')

@section('title', __('admin.notifications.title'))

@section('content')
<div class="container-fluid py-4">

    {{-- Page header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-1"><i class="bi bi-bell-fill text-primary me-2"></i>{{ __('admin.notifications.title') }}</h1>
            <p class="text-muted mb-0 small">{{ __('admin.notifications.subtitle') }}</p>
        </div>
        <a href="{{ route('admin.notifications.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-megaphone me-1"></i>{{ __('admin.notifications.new_announcement') }}
        </a>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Stats cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-2 fw-bold text-primary">{{ number_format($stats['total']) }}</div>
                <div class="text-muted small">{{ __('admin.notifications.stats.total_sent') }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-2 fw-bold text-success">{{ number_format($stats['sent']) }}</div>
                <div class="text-muted small">{{ __('admin.notifications.stats.delivered') }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-2 fw-bold text-danger">{{ number_format($stats['failed']) }}</div>
                <div class="text-muted small">{{ __('admin.notifications.stats.failed') }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-2 fw-bold text-info">{{ number_format($stats['today']) }}</div>
                <div class="text-muted small">{{ __('admin.notifications.stats.today') }}</div>
            </div>
        </div>
    </div>

    {{-- Quick Action Triggers --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-lightning-charge-fill text-warning me-2"></i>{{ __('admin.notifications.quick_triggers') }}
        </div>
        <div class="card-body">
            <div class="row g-2">
                {{-- Appointment Reminders --}}
                <div class="col-md-4">
                    <form action="{{ route('admin.notifications.send-appointment-reminders') }}" method="POST">
                        @csrf
                        <input type="hidden" name="channels[]" value="database">
                        <input type="hidden" name="channels[]" value="email">
                        <button class="btn btn-outline-primary w-100 btn-sm py-2" type="submit"
                            onclick="return confirm('{{ __('admin.notifications.confirm_send_appointment_reminders') }}')">
                            <i class="bi bi-calendar-check me-1"></i>
                            {{ __('admin.notifications.send_appointment_reminders') }}<br>
                            <small class="text-muted fw-normal">{{ __('admin.notifications.send_appointment_reminders_help') }}</small>
                        </button>
                    </form>
                </div>
                {{-- Billing Reminders --}}
                <div class="col-md-4">
                    <form action="{{ route('admin.notifications.send-billing-reminders') }}" method="POST">
                        @csrf
                        <input type="hidden" name="channels[]" value="database">
                        <input type="hidden" name="channels[]" value="email">
                        <button class="btn btn-outline-warning w-100 btn-sm py-2" type="submit"
                            onclick="return confirm('{{ __('admin.notifications.confirm_send_billing_reminders') }}')">
                            <i class="bi bi-receipt-cutoff me-1"></i>
                            {{ __('admin.notifications.send_billing_reminders') }}<br>
                            <small class="text-muted fw-normal">{{ __('admin.notifications.send_billing_reminders_help') }}</small>
                        </button>
                    </form>
                </div>
                {{-- Announcement --}}
                <div class="col-md-4">
                    <a href="{{ route('admin.notifications.create') }}"
                       class="btn btn-outline-success w-100 btn-sm py-2 d-block text-center">
                        <i class="bi bi-megaphone me-1"></i>
                        {{ __('admin.notifications.compose_announcement') }}<br>
                        <small class="text-muted fw-normal">{{ __('admin.notifications.compose_announcement_help') }}</small>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-funnel me-2 text-secondary"></i>{{ __('admin.notifications.filter_logs') }}
        </div>
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-2">
                    <label class="form-label small">{{ __('admin.notifications.channel') }}</label>
                    <select name="channel" class="form-select form-select-sm">
                        <option value="">{{ __('admin.notifications.all_channels') }}</option>
                        @foreach($channels as $ch)
                            <option value="{{ $ch }}" @selected(($filters['channel'] ?? '') === $ch)>
                                {{ __('admin.notifications.channels.' . $ch) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">{{ __('admin.notifications.type') }}</label>
                    <select name="notification_type" class="form-select form-select-sm">
                        <option value="">{{ __('admin.notifications.all_types') }}</option>
                        @foreach($notificationTypes as $type)
                            <option value="{{ $type->value }}" @selected(($filters['notification_type'] ?? '') === $type->value)>
                                {{ $type->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">{{ __('admin.notifications.status') }}</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">{{ __('admin.notifications.all_statuses') }}</option>
                        @foreach(['pending','sent','failed','delivered'] as $st)
                            <option value="{{ $st }}" @selected(($filters['status'] ?? '') === $st)>{{ __('admin.notifications.statuses.' . $st) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">{{ __('admin.notifications.from') }}</label>
                    <input type="date" name="date_from" class="form-control form-control-sm"
                           value="{{ $filters['date_from'] ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">{{ __('admin.notifications.to') }}</label>
                    <input type="date" name="date_to" class="form-control form-control-sm"
                           value="{{ $filters['date_to'] ?? '' }}">
                </div>
                <div class="col-md-1 d-flex align-items-end gap-1">
                    <button class="btn btn-primary btn-sm w-100">{{ __('admin.notifications.filter') }}</button>
                </div>
            </div>
            @if(array_filter($filters))
                <div class="mt-2">
                    <a href="{{ route('admin.notifications.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i>{{ __('admin.notifications.clear') }}
                    </a>
                </div>
            @endif
        </div>
    </form>

    {{-- Logs table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold"><i class="bi bi-journal-text me-2 text-secondary"></i>{{ __('admin.notifications.log_history') }}</span>
            <span class="badge bg-secondary">{{ __('admin.notifications.records_count', ['count' => $logs->total()]) }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>{{ __('admin.notifications.type') }}</th>
                        <th>{{ __('admin.notifications.title_label') }}</th>
                        <th>{{ __('admin.notifications.channel') }}</th>
                        <th>{{ __('admin.notifications.recipient') }}</th>
                        <th>{{ __('admin.notifications.status') }}</th>
                        <th>{{ __('admin.notifications.triggered_by') }}</th>
                        <th>{{ __('admin.notifications.sent_at') }}</th>
                        <th class="text-end">{{ __('admin.notifications.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="text-muted small">{{ $log->id }}</td>
                            <td>
                                <span class="badge bg-light text-dark border small">
                                    {{ str_replace('_', ' ', ucfirst($log->notification_type)) }}
                                </span>
                            </td>
                            <td class="fw-medium" style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                {{ $log->title }}
                            </td>
                            <td>
                                @php
                                    $chIcon = match($log->channel) {
                                        'email'    => 'bi-envelope-fill text-info',
                                        'sms'      => 'bi-chat-dots-fill text-success',
                                        'push'     => 'bi-phone-fill text-warning',
                                        'database',
                                        'in_app'   => 'bi-bell-fill text-primary',
                                        default    => 'bi-circle text-secondary',
                                    };
                                @endphp
                                <i class="bi {{ $chIcon }} me-1"></i>
                                {{ __('admin.notifications.channels.' . $log->channel) }}
                            </td>
                            <td class="small text-muted">
                                {{ class_basename($log->notifiable_type) }} #{{ $log->notifiable_id }}
                            </td>
                            <td>
                                @php
                                    $badge = match($log->status) {
                                        'sent','delivered' => 'bg-success',
                                        'failed'           => 'bg-danger',
                                        'pending'          => 'bg-warning text-dark',
                                        default            => 'bg-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $badge }}">{{ __('admin.notifications.statuses.' . $log->status) }}</span>
                            </td>
                            <td class="small text-muted">
                                {{ $log->triggered_by_type ?? __('admin.notifications.not_available') }}
                                @if($log->triggered_by)
                                    <br><span class="text-primary">{{ __('admin.notifications.user_number', ['id' => $log->triggered_by]) }}</span>
                                @endif
                            </td>
                            <td class="small text-muted">
                                {{ optional($log->sent_at)?->format('M j, Y H:i') ?? __('admin.notifications.not_available') }}
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.notifications.show', $log->id) }}"
                                   class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-bell-slash fs-2 d-block mb-2"></i>
                                {{ __('admin.notifications.no_logs') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="card-footer bg-white">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

