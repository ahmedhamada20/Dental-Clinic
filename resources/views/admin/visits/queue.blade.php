@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">{{ __('visits.today_queue') }}</h2>
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

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="d-flex">
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 -9a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 7v5" /><path d="M12 16v.01" /></svg>
                    </div>
                    <div class="ms-3">
                        <h3 class="alert-title">{{ __('visits.error') }}</h3>
                        <div class="text-secondary">{{ session('error') }}</div>
                    </div>
                </div>
                <a class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('common.close') }}"></a>
            </div>
        @endif

        <div class="row">
            <!-- Queue Section -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('visits.patient_queue') }}</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>{{ __('visits.ticket_no') }}</th>
                                    <th>{{ __('visits.patient_name') }}</th>
                                    <th>{{ __('common.status') }}</th>
                                    <th>{{ __('visits.created_at_label') }}</th>
                                    <th>{{ __('common.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($queue->isEmpty())
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            {{ __('visits.no_patients_queue') }}
                                        </td>
                                    </tr>
                                @else
                                    @foreach ($queue as $ticket)
                                        <tr>
                                            <td>
                                                <span class="badge badge-lg badge-primary">{{ $ticket->ticket_number }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ $ticket->patient?->full_name ?? $ticket->patient?->displayName ?? __('common.not_available') }}</strong>
                                            </td>
                                            <td>
                                                @if ($ticket->status->value === 'issued')
                                                    <span class="badge bg-info">{{ __('visits.waiting') }}</span>
                                                @elseif ($ticket->status->value === 'called')
                                                    <span class="badge bg-warning">{{ __('visits.called') }}</span>
                                                @elseif ($ticket->status->value === 'in_service')
                                                    <span class="badge bg-success">{{ __('visits.in_service') }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $ticket->status->label() }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $ticket->created_at->format('H:i:s') }}
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    @if ($ticket->status->value === 'issued')
                                                        <form method="POST" action="{{ route('admin.visits.call-from-queue', $ticket) }}" style="display: inline;">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-info" title="{{ __('visits.call') }}">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M22 16.92v3a2 2 0 0 1 -2.18 2 19.79 19.79 0 0 1 -8.63 -3.07 19.5 19.5 0 0 1 -6 -6 19.79 19.79 0 0 1 -3.07 -8.67a2 2 0 0 1 2 -2.18h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81a2 2 0 0 1 -.45 2.11l-1.65 1.65a16 16 0 0 0 6 6l1.65 -1.65a2 2 0 0 1 2.11 -.45 12.84 12.84 0 0 0 2.81 .7a2 2 0 0 1 1.72 2z" /></svg>
                                                                {{ __('visits.call') }}
                                                            </button>
                                                        </form>
                                                    @endif
                                                    @if ($ticket->visit)
                                                        <a href="{{ route('admin.visits.show', $ticket->visit) }}" class="btn btn-sm btn-primary" title="{{ __('visits.view_details') }}">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 9v3m0 3v.01" /></svg>
                                                            {{ __('common.view') }}
                                                        </a>
                                                    @else
                                                        <button type="button" class="btn btn-sm btn-primary" disabled title="{{ __('visits.visit_not_created') }}">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 9v3m0 3v.01" /></svg>
                                                            {{ __('common.view') }}
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Active Visits Section -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('visits.active_visits') }}</h3>
                    </div>
                    <div class="card-body">
                        @if ($activeVisits->isEmpty())
                            <div class="text-center text-muted py-4">
                                <p>{{ __('visits.no_active_visits') }}</p>
                            </div>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach ($activeVisits as $visit)
                                    <div class="list-group-item">
                                        <div class="row align-items-center">
                                            <div class="col-auto">
                                                <span class="badge bg-warning">{{ __('visits.in_progress') }}</span>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <strong class="text-body">{{ $visit->patient?->full_name ?? $visit->patient?->displayName ?? __('common.not_available') }}</strong>
                                            <div class="text-muted small">
                                                {{ __('visits.doctor') }}: {{ $visit->doctor?->display_name ?? $visit->doctor?->full_name ?? __('common.not_available') }}<br>
                                                {{ __('visits.started') }}: {{ $visit->start_at?->format('H:i') ?? __('common.not_available') }}
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <a href="{{ route('admin.visits.show', $visit) }}" class="btn btn-sm btn-primary">
                                                {{ __('common.view') }}
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

