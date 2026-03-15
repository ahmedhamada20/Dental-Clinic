@extends('admin.layouts.app')

@section('title', __('admin.sidebar.treatment_plans'))

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">{{ __('admin.sidebar.treatment_plans') }}</h1>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('admin.billing.patient') }}</th>
                        <th>{{ __('admin.date') }}</th>
                        <th>{{ __('admin.status') }}</th>
                        <th class="text-end">{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($treatmentPlans as $plan)
                        <tr>
                            @php
                                $statusLabel = is_object($plan->status) && method_exists($plan->status, 'label')
                                    ? $plan->status->label()
                                    : ($plan->status ? str_replace('_', ' ', ucfirst((string) $plan->status)) : '-');
                            @endphp
                            <td>{{ $plan->treatment_plan_no ?? $plan->id }}</td>
                            <td>{{ $plan->patient?->full_name ?? __('common.not_available') }}</td>
                            <td>{{ optional($plan->created_at)->format('Y-m-d') }}</td>
                            <td>{{ $statusLabel }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.treatment-plans.show', $plan) }}" class="btn btn-sm btn-outline-primary">{{ __('common.view') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-3 text-muted">{{ __('treatment_plans.no_plans') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">{{ $treatmentPlans->links() }}</div>
    </div>
</div>
@endsection

