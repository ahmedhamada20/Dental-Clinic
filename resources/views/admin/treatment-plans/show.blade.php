@extends('admin.layouts.app')

@section('title', __('admin.sidebar.treatment_plans'))

@section('content')
@php
    $statusLabel = is_object($treatmentPlan->status) && method_exists($treatmentPlan->status, 'label')
        ? $treatmentPlan->status->label()
        : ($treatmentPlan->status ? str_replace('_', ' ', ucfirst((string) $treatmentPlan->status)) : '-');
@endphp
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">{{ __('admin.sidebar.treatment_plans') }} #{{ $treatmentPlan->treatment_plan_no ?? $treatmentPlan->id }}</h1>
        <a href="{{ route('admin.treatment-plans.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('admin.back') }}</a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">{{ __('admin.billing.patient') }}</label>
                    <input class="form-control" value="{{ $treatmentPlan->patient?->full_name ?? __('common.not_available') }}" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('admin.status') }}</label>
                    <input class="form-control" value="{{ $statusLabel }}" readonly>
                </div>
                <div class="col-12">
                    <label class="form-label">{{ __('treatment_plans.notes') }}</label>
                    <textarea class="form-control" rows="4" readonly>{{ $treatmentPlan->notes ?? '' }}</textarea>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

