@extends('admin.layouts.app')

@section('title', __('admin.service_categories.create_title'))

@section('breadcrumb')
    <nav aria-label="{{ __('admin.layout.breadcrumb_label') }}">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">{{ __('admin.sidebar.dashboard') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.service-categories.index') }}">{{ __('admin.service_categories.title') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('common.create') }}</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>{{ __('admin.service_categories.create_title') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.service-categories.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="medical_specialty_id" class="form-label">{{ __('admin.service_categories.fields.medical_specialty_id') }} <span class="text-danger">*</span></label>
                            <select class="form-select @error('medical_specialty_id') is-invalid @enderror" id="medical_specialty_id" name="medical_specialty_id" required>
                                <option value="">{{ __('admin.service_categories.placeholders.select_specialty') }}</option>
                                @foreach($specialties as $specialty)
                                    <option value="{{ $specialty->id }}" {{ (string) old('medical_specialty_id') === (string) $specialty->id ? 'selected' : '' }}>{{ $specialty->name }}</option>
                                @endforeach
                            </select>
                            @error('medical_specialty_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="name_ar" class="form-label">{{ __('admin.service_categories.fields.name_ar') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name_ar') is-invalid @enderror"
                                   id="name_ar" name="name_ar" value="{{ old('name_ar') }}" required>
                            @error('name_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="name_en" class="form-label">{{ __('admin.service_categories.fields.name_en') }}</label>
                            <input type="text" class="form-control @error('name_en') is-invalid @enderror"
                                   id="name_en" name="name_en" value="{{ old('name_en') }}">
                            @error('name_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="sort_order" class="form-label">{{ __('admin.service_categories.fields.sort_order') }}</label>
                            <input type="number" class="form-control @error('sort_order') is-invalid @enderror"
                                   id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" min="0">
                            @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                       value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">{{ __('admin.service_categories.fields.is_active') }}</label>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle me-1"></i>{{ __('admin.service_categories.actions.create') }}
                            </button>
                            <a href="{{ route('admin.service-categories.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i>{{ __('common.cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

