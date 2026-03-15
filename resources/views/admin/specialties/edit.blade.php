@extends('admin.layouts.app')

@section('title', __('specialties.edit_title'))

@section('breadcrumb')
    <nav aria-label="{{ __('admin.layout.breadcrumb_label') }}">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">{{ __('admin.sidebar.dashboard') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.specialties.index') }}">{{ __('specialties.title') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('common.edit') }}</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header bg-primary text-white">{{ __('specialties.edit_title') }}</div>
                <div class="card-body">
                    <form action="{{ route('admin.specialties.update', $specialty) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label">{{ __('specialties.fields.name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $specialty->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('specialties.fields.icon') }}</label>
                            <input type="text" name="icon" class="form-control @error('icon') is-invalid @enderror" value="{{ old('icon', $specialty->icon) }}" placeholder="{{ __('specialties.placeholders.icon') }}">
                            @error('icon')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('specialties.fields.description') }}</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $specialty->description) }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $specialty->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">{{ __('specialties.fields.is_active') }}</label>
                        </div>

                        <div class="d-flex gap-2">
                            <button class="btn btn-primary">{{ __('common.save') }}</button>
                            <a href="{{ route('admin.specialties.index') }}" class="btn btn-secondary">{{ __('common.cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

