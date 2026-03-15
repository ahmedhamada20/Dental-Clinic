@extends('admin.layouts.app')

@section('title', __('admin.audit_logs.details_title'))

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">{{ __('common.breadcrumb_dashboard') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.audit-logs.index') }}">{{ __('admin.audit_logs.title') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('admin.audit_logs.log_number', ['id' => $log->id]) }}</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>{{ __('admin.audit_logs.log_number', ['id' => $log->id]) }}</h5>
            <a href="{{ route('admin.audit-logs.index', request()->query()) }}" class="btn btn-sm btn-light">{{ __('admin.audit_logs.back_to_logs') }}</a>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="border rounded p-3 h-100">
                        <h6 class="text-uppercase text-muted">{{ __('admin.audit_logs.summary') }}</h6>
                        <dl class="row mb-0">
                            <dt class="col-sm-4">{{ __('admin.audit_logs.actor') }}</dt>
                            <dd class="col-sm-8">{{ $log->actor_name }}</dd>
                            <dt class="col-sm-4">{{ __('admin.audit_logs.module') }}</dt>
                            <dd class="col-sm-8">{{ ucfirst(str_replace(['-', '.'], ' ', $log->module)) }}</dd>
                            <dt class="col-sm-4">{{ __('admin.audit_logs.action') }}</dt>
                            <dd class="col-sm-8">{{ ucfirst(str_replace('_', ' ', $log->action)) }}</dd>
                            <dt class="col-sm-4">{{ __('admin.audit_logs.entity') }}</dt>
                            <dd class="col-sm-8">{{ $log->entity_label }}</dd>
                            <dt class="col-sm-4">{{ __('admin.audit_logs.when') }}</dt>
                            <dd class="col-sm-8">{{ optional($log->created_at)->format('M d, Y H:i:s') }}</dd>
                            <dt class="col-sm-4">IP</dt>
                            <dd class="col-sm-8">{{ $log->ip_address ?: '—' }}</dd>
                        </dl>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded p-3 h-100">
                        <h6 class="text-uppercase text-muted">{{ __('admin.audit_logs.old_values') }}</h6>
                        @if(!empty($log->old_values))
                            <pre class="small mb-0">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                        @else
                            <p class="text-muted mb-0">{{ __('admin.audit_logs.no_old_values') }}</p>
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded p-3 h-100">
                        <h6 class="text-uppercase text-muted">{{ __('admin.audit_logs.new_values') }}</h6>
                        @if(!empty($log->new_values))
                            <pre class="small mb-0">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                        @else
                            <p class="text-muted mb-0">{{ __('admin.audit_logs.no_new_values') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

