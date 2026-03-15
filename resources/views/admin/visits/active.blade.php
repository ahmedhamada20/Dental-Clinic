@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">{{ __('visits.active_visits') }}</h2>
            </div>
        </div>
    </div>
</div>

<div class="page-wrapper">
    <div class="container-fluid">
        <!-- Alerts -->
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="d-flex">
                    <div>
                        <strong>{{ __('visits.error') }}</strong>
                    </div>
                    <div class="ms-3">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('common.close') }}"></button>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <div class="d-flex">
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9" /><path d="M9 12l2 2l4 -4" /></svg>
                    </div>
                    <div class="ms-3">
                        <h3 class="alert-title">{{ __('visits.success') }}</h3>
                        <div class="text-secondary">{{ session('success') }}</div>
                    </div>
                </div>
                <a class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('common.close') }}"></a>
            </div>
        @endif

        <div class="row">
            @if ($activeVisits->isEmpty())
                <div class="col-12">
                    <div class="alert alert-info">
                        <div class="d-flex">
                            <div>
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="12" r="9" /><line x1="12" y1="8" x2="12" y2="12" /><line x1="12" y1="16" x2="12.01" y2="16" /></svg>
                            </div>
                            <div class="ms-3">
                                <h3 class="alert-title">{{ __('visits.no_active_visits') }}</h3>
                                <div class="text-secondary">{{ __('visits.no_active_visits_description') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                @foreach ($activeVisits as $visit)
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="badge bg-warning">{{ __('visits.in_progress') }}</div>
                                    <small class="text-muted">{{ $visit->start_at?->format('H:i:s') ?? __('common.not_available') }}</small>
                                </div>
                                <h4 class="card-title">{{ $visit->patient?->full_name ?? $visit->patient?->displayName ?? __('common.not_available') }}</h4>
                                <div class="text-muted small mb-3">
                                    <div><strong>{{ __('visits.visit_no') }}:</strong> {{ $visit->visit_no }}</div>
                                    <div><strong>{{ __('visits.doctor') }}:</strong> {{ $visit->doctor?->display_name ?? $visit->doctor?->full_name ?? __('visits.unassigned') }}</div>
                                    <div><strong>{{ __('visits.duration') }}:</strong>
                                        @if ($visit->start_at)
                                            {{ $visit->start_at->diff(now())->format('%I min %s sec') }}
                                        @else
                                            {{ __('common.not_available') }}
                                        @endif
                                    </div>
                                </div>
                                <div class="btn-group w-100" role="group">
                                    <a href="{{ route('admin.visits.show', $visit) }}" class="btn btn-sm btn-primary">
                                        {{ __('visits.view_details') }}
                                    </a>
                                    <form method="POST" action="{{ route('admin.visits.complete', $visit) }}" style="display: inline; flex: 1;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success w-100" onclick="return confirm('{{ __('visits.complete_confirm') }}')">
                                            {{ __('visits.complete') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
@endsection

