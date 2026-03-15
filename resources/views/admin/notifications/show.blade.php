@extends('admin.layouts.app')

@section('title', __('admin.notifications.log_detail_title'))

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.notifications.index') }}" class="btn btn-sm btn-outline-secondary me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 class="h4 mb-0"><i class="bi bi-journal-text text-primary me-2"></i>{{ __('admin.notifications.log_number', ['id' => $log->id]) }}</h1>
            <p class="text-muted mb-0 small">{{ __('admin.notifications.log_detail_subtitle') }}</p>
        </div>
    </div>

    <div class="row g-4">
        {{-- Main detail card --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">{{ __('admin.notifications.dispatch_record') }}</span>
                    @php
                        $badge = match($log->status) {
                            'sent','delivered' => 'bg-success',
                            'failed'           => 'bg-danger',
                            'pending'          => 'bg-warning text-dark',
                            default            => 'bg-secondary',
                        };
                    @endphp
                    <span class="badge {{ $badge }} fs-6">{{ __('admin.notifications.statuses.' . $log->status) }}</span>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3 text-muted">{{ __('admin.notifications.title_label') }}</dt>
                        <dd class="col-sm-9 fw-medium">{{ $log->title }}</dd>

                        <dt class="col-sm-3 text-muted">{{ __('admin.notifications.body') }}</dt>
                        <dd class="col-sm-9">{{ $log->body }}</dd>

                        <dt class="col-sm-3 text-muted">{{ __('admin.notifications.type') }}</dt>
                        <dd class="col-sm-9">
                            <span class="badge bg-light text-dark border">
                                {{ str_replace('_', ' ', ucfirst($log->notification_type)) }}
                            </span>
                        </dd>

                        <dt class="col-sm-3 text-muted">{{ __('admin.notifications.channel') }}</dt>
                        <dd class="col-sm-9">
                            @php
                                $chIcon = match($log->channel) {
                                    'email'  => 'bi-envelope-fill text-info',
                                    'sms'    => 'bi-chat-dots-fill text-success',
                                    'push'   => 'bi-phone-fill text-warning',
                                    default  => 'bi-bell-fill text-primary',
                                };
                            @endphp
                            <i class="bi {{ $chIcon }} me-1"></i>{{ __('admin.notifications.channels.' . $log->channel) }}
                        </dd>

                        <dt class="col-sm-3 text-muted">{{ __('admin.notifications.recipient') }}</dt>
                        <dd class="col-sm-9">
                            {{ class_basename($log->notifiable_type) }} #{{ $log->notifiable_id }}
                            @if($log->meta)
                                @if(!empty($log->meta['email']))
                                    &nbsp;<span class="text-muted">({{ $log->meta['email'] }})</span>
                                @elseif(!empty($log->meta['phone']))
                                    &nbsp;<span class="text-muted">({{ $log->meta['phone'] }})</span>
                                @endif
                            @endif
                        </dd>

                        @if($log->error_message)
                        <dt class="col-sm-3 text-muted">{{ __('admin.notifications.error') }}</dt>
                        <dd class="col-sm-9 text-danger">{{ $log->error_message }}</dd>
                        @endif

                        <dt class="col-sm-3 text-muted">{{ __('admin.notifications.sent_at') }}</dt>
                        <dd class="col-sm-9">{{ optional($log->sent_at)?->format('D, M j Y H:i:s') ?? __('admin.notifications.not_available') }}</dd>

                        <dt class="col-sm-3 text-muted">{{ __('admin.notifications.created_at') }}</dt>
                        <dd class="col-sm-9">{{ optional($log->created_at)?->format('D, M j Y H:i:s') }}</dd>
                    </dl>
                </div>
            </div>

            {{-- Meta / extra payload --}}
            @if($log->meta)
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-code-slash me-2 text-secondary"></i>{{ __('admin.notifications.meta_payload') }}
                </div>
                <div class="card-body">
                    <pre class="bg-light rounded p-3 mb-0 small"><code>{{ json_encode($log->meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                </div>
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-person-badge me-2 text-secondary"></i>{{ __('admin.notifications.triggered_by') }}
                </div>
                <div class="card-body">
                    @php
                        $triggerType = $log->triggered_by_type ?? 'auto';
                        $triggerTypeLabel = match($triggerType) {
                            'auto' => __('admin.notifications.trigger_types.auto'),
                            'system' => __('admin.notifications.trigger_types.system'),
                            'user' => __('admin.notifications.trigger_types.user'),
                            default => $triggerType,
                        };
                    @endphp
                    <p class="mb-1">
                        <strong>{{ __('admin.notifications.type') }}:</strong>
                        <span class="badge bg-light text-dark border">{{ $triggerTypeLabel }}</span>
                    </p>
                    @if($log->triggeredBy)
                        <p class="mb-0">
                            <strong>{{ __('admin.notifications.user') }}:</strong> {{ $log->triggeredBy->name ?? __('admin.notifications.user_number', ['id' => $log->triggered_by]) }}
                        </p>
                    @elseif($log->triggered_by)
                        <p class="mb-0"><strong>{{ __('admin.notifications.user_id') }}:</strong> {{ $log->triggered_by }}</p>
                    @else
                        <p class="mb-0 text-muted">{{ __('admin.notifications.system_scheduled') }}</p>
                    @endif
                </div>
            </div>

            {{-- Delivery Timeline --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-clock-history me-2 text-secondary"></i>{{ __('admin.notifications.timeline') }}
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex mb-3">
                            <div class="me-3 text-primary"><i class="bi bi-plus-circle-fill fs-5"></i></div>
                            <div>
                                <div class="fw-medium">{{ __('admin.notifications.created') }}</div>
                                <div class="text-muted small">{{ optional($log->created_at)?->format('M j, Y H:i:s') }}</div>
                            </div>
                        </li>
                        @if($log->sent_at)
                        <li class="d-flex mb-3">
                            <div class="me-3 text-success"><i class="bi bi-send-check-fill fs-5"></i></div>
                            <div>
                                <div class="fw-medium">{{ __('admin.notifications.sent') }}</div>
                                <div class="text-muted small">{{ optional($log->sent_at)?->format('M j, Y H:i:s') }}</div>
                            </div>
                        </li>
                        @endif
                        @if($log->status === 'failed')
                        <li class="d-flex">
                            <div class="me-3 text-danger"><i class="bi bi-x-circle-fill fs-5"></i></div>
                            <div>
                                <div class="fw-medium text-danger">{{ __('admin.notifications.statuses.failed') }}</div>
                                <div class="text-muted small">{{ $log->error_message }}</div>
                            </div>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

