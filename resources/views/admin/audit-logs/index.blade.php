@extends('admin.layouts.app')

@section('title', __('admin.audit_logs.title'))

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">{{ __('common.breadcrumb_dashboard') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('admin.audit_logs.title') }}</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card mb-4">
        <div class="card-header bg-dark text-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="mb-1"><i class="bi bi-journal-text me-2"></i>{{ __('admin.audit_logs.title') }}</h5>
                    <small class="text-white-50">{{ __('admin.audit_logs.subtitle') }}</small>
                </div>
                <span class="badge bg-light text-dark">{{ __('admin.audit_logs.entries_count', ['count' => $logs->total()]) }}</span>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">{{ __('common.search') }}</label>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="{{ __('admin.audit_logs.search_placeholder') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ __('admin.audit_logs.module') }}</label>
                    <select name="module" class="form-select">
                        <option value="">{{ __('admin.audit_logs.all_modules') }}</option>
                        @foreach($modules as $module)
                            <option value="{{ $module }}" @selected(($filters['module'] ?? '') === $module)>{{ ucfirst(str_replace(['-', '.'], ' ', $module)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ __('admin.audit_logs.action') }}</label>
                    <select name="action" class="form-select">
                        <option value="">{{ __('admin.audit_logs.all_actions') }}</option>
                        @foreach($actions as $action)
                            <option value="{{ $action }}" @selected(($filters['action'] ?? '') === $action)>{{ ucfirst(str_replace(['-', '_'], ' ', $action)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ __('admin.audit_logs.actor') }}</label>
                    <select name="actor_id" class="form-select">
                        <option value="">{{ __('admin.audit_logs.all_actors') }}</option>
                        @foreach($actors as $actor)
                            <option value="{{ $actor->id }}" @selected((string) ($filters['actor_id'] ?? '') === (string) $actor->id)>{{ $actor->display_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label">{{ __('admin.audit_logs.from') }}</label>
                    <input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="form-control">
                </div>
                <div class="col-md-1">
                    <label class="form-label">{{ __('admin.audit_logs.to') }}</label>
                    <input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="form-control">
                </div>
                <div class="col-md-1">
                    <label class="form-label">{{ __('admin.audit_logs.rows') }}</label>
                    <select name="per_page" class="form-select">
                        @foreach([20, 50, 100] as $size)
                            <option value="{{ $size }}" @selected((int) ($filters['per_page'] ?? 20) === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-2"></i>{{ __('common.filter') }}</button>
                    <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-outline-secondary">{{ __('common.reset') }}</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>{{ __('admin.audit_logs.when') }}</th>
                        <th>{{ __('admin.audit_logs.actor') }}</th>
                        <th>{{ __('admin.audit_logs.module') }}</th>
                        <th>{{ __('admin.audit_logs.action') }}</th>
                        <th>{{ __('admin.audit_logs.entity') }}</th>
                        <th>IP</th>
                        <th class="text-end">{{ __('admin.audit_logs.details') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td><span class="badge bg-secondary">{{ $log->id }}</span></td>
                            <td>
                                <div>{{ optional($log->created_at)->format('M d, Y H:i:s') }}</div>
                                <small class="text-muted">{{ optional($log->created_at)->diffForHumans() }}</small>
                            </td>
                            <td>
                                <div>{{ $log->actor_name }}</div>
                                <small class="text-muted">{{ $log->actor_type }}</small>
                            </td>
                            <td>{{ ucfirst(str_replace(['-', '.'], ' ', $log->module)) }}</td>
                            <td><span class="badge bg-info text-dark">{{ ucfirst(str_replace('_', ' ', $log->action)) }}</span></td>
                            <td>
                                <div>{{ $log->entity_label }}</div>
                                <small class="text-muted">{{ $log->entity_type }}</small>
                            </td>
                            <td>{{ $log->ip_address ?: '—' }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.audit-logs.show', $log->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i>{{ __('common.view') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                {{ __('admin.audit_logs.empty') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="card-footer bg-light">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

