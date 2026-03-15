@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">{{ __('visits.completed_today') }}</h2>
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

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('visits.today_completed_visits') }}</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>{{ __('visits.visit_no') }}</th>
                            <th>{{ __('visits.patient_name') }}</th>
                            <th>{{ __('visits.doctor') }}</th>
                            <th>{{ __('visits.started') }}</th>
                            <th>{{ __('visits.completed_at') }}</th>
                            <th>{{ __('visits.duration') }}</th>
                            <th>{{ __('visits.diagnosis') }}</th>
                            <th>{{ __('common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($completedVisits->isEmpty())
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    {{ __('visits.no_completed_today') }}
                                </td>
                            </tr>
                        @else
                            @foreach ($completedVisits as $visit)
                                <tr>
                                    <td>
                                        <strong>{{ $visit->visit_no }}</strong>
                                    </td>
                                    <td>
                                        {{ $visit->patient?->full_name ?? $visit->patient?->displayName ?? __('common.not_available') }}
                                    </td>
                                    <td>
                                        {{ $visit->doctor?->display_name ?? $visit->doctor?->full_name ?? __('common.not_available') }}
                                    </td>
                                    <td>
                                        {{ $visit->start_at?->format('H:i:s') ?? __('common.not_available') }}
                                    </td>
                                    <td>
                                        {{ $visit->end_at?->format('H:i:s') ?? __('common.not_available') }}
                                    </td>
                                    <td>
                                        @if ($visit->start_at && $visit->end_at)
                                            <span class="badge bg-info">{{ $visit->start_at->diff($visit->end_at)->format('%I:%S') }}</span>
                                        @else
                                            {{ __('common.not_available') }}
                                        @endif
                                    </td>
                                    <td>
                                        <span title="{{ $visit->diagnosis }}">
                                            {{ Str::limit($visit->diagnosis ?? __('common.not_available'), 30) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.visits.show', $visit) }}" class="btn btn-sm btn-primary">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 9v3m0 3v.01" /></svg>
                                            {{ __('common.view') }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

