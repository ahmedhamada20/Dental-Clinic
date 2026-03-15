@extends('admin.layouts.app')

@section('title', __('admin.promotions.edit_title'))

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">{{ __('admin.sidebar.dashboard') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.promotions.index') }}">{{ __('admin.promotions.title') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('admin.promotions.actions.edit') }}</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>{{ __('admin.promotions.edit_heading', ['title' => $promotion->title_en ?? $promotion->title_ar]) }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.promotions.update', $promotion) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="title_ar" class="form-label">{{ __('admin.promotions.fields.title_ar') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('title_ar') is-invalid @enderror"
                                       id="title_ar" name="title_ar" value="{{ old('title_ar', $promotion->title_ar) }}" required>
                                @error('title_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="title_en" class="form-label">{{ __('admin.promotions.fields.title_en') }}</label>
                                <input type="text" class="form-control @error('title_en') is-invalid @enderror"
                                       id="title_en" name="title_en" value="{{ old('title_en', $promotion->title_en) }}">
                                @error('title_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="code" class="form-label">{{ __('admin.promotions.fields.code') }}</label>
                                <input type="text" class="form-control font-monospace @error('code') is-invalid @enderror"
                                       id="code" name="code" value="{{ old('code', $promotion->code) }}">
                                @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="promotion_type" class="form-label">{{ __('admin.promotions.fields.promotion_type') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('promotion_type') is-invalid @enderror"
                                        id="promotion_type" name="promotion_type" required>
                                    <option value="">{{ __('admin.promotions.placeholders.select_type') }}</option>
                                    @foreach($promotionTypes as $pt)
                                        <option value="{{ $pt['value'] }}"
                                            {{ old('promotion_type', $promotion->promotion_type?->value) === $pt['value'] ? 'selected' : '' }}>
                                            {{ $pt['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('promotion_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="value" class="form-label">{{ __('admin.promotions.fields.value') }}</label>
                                <input type="number" step="0.01" min="0"
                                       class="form-control @error('value') is-invalid @enderror"
                                       id="value" name="value" value="{{ old('value', $promotion->value) }}">
                                @error('value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="starts_at" class="form-label">{{ __('admin.promotions.fields.starts_at') }} <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control @error('starts_at') is-invalid @enderror"
                                       id="starts_at" name="starts_at"
                                       value="{{ old('starts_at', $promotion->starts_at?->format('Y-m-d\TH:i')) }}" required>
                                @error('starts_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="ends_at" class="form-label">{{ __('admin.promotions.fields.ends_at') }} <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control @error('ends_at') is-invalid @enderror"
                                       id="ends_at" name="ends_at"
                                       value="{{ old('ends_at', $promotion->ends_at?->format('Y-m-d\TH:i')) }}" required>
                                @error('ends_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4 mb-3 d-flex align-items-end">
                                <div class="d-flex flex-column gap-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="applies_once"
                                               name="applies_once" value="1"
                                               {{ old('applies_once', $promotion->applies_once) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="applies_once">{{ __('admin.promotions.fields.applies_once') }}</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_active"
                                               name="is_active" value="1"
                                               {{ old('is_active', $promotion->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">{{ __('admin.promotions.status.active') }}</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="notes" class="form-label">{{ __('admin.promotions.fields.notes') }}</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror"
                                          id="notes" name="notes" rows="2">{{ old('notes', $promotion->notes) }}</textarea>
                                @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <!-- Services -->
                            <div class="col-12 mb-4">
                                <label class="form-label">
                                    {{ __('admin.promotions.assigned_services') }}
                                    <small class="text-muted">({{ __('admin.promotions.assigned_services_help') }})</small>
                                </label>
                                <div class="border rounded p-3" style="max-height:220px;overflow-y:auto;">
                                    @forelse($services as $svc)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                   name="service_ids[]" value="{{ $svc->id }}"
                                                   id="svc_{{ $svc->id }}"
                                                   {{ in_array($svc->id, old('service_ids', $selectedServiceIds)) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="svc_{{ $svc->id }}">
                                                {{ $svc->name_en ?? $svc->name_ar }}
                                                <small class="text-muted">({{ number_format($svc->default_price, 2) }})</small>
                                            </label>
                                        </div>
                                    @empty
                                        <p class="text-muted mb-0">{{ __('admin.promotions.no_active_services') }}</p>
                                    @endforelse
                                </div>
                                @error('service_ids')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i>{{ __('admin.promotions.actions.save_changes') }}
                            </button>
                            <a href="{{ route('admin.promotions.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i>{{ __('admin.cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

