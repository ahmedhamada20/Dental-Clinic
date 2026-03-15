@extends('admin.layouts.app')

@section('title', __('services.create'))

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.services.index') }}">Services</a></li>
            <li class="breadcrumb-item active" aria-current="page">Create</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Create New Service</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.services.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <!-- Name AR -->
                            <div class="col-md-6 mb-3">
                                <label for="name_ar" class="form-label">Name (Arabic) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name_ar') is-invalid @enderror"
                                       id="name_ar" name="name_ar" value="{{ old('name_ar') }}" required>
                                @error('name_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <!-- Name EN -->
                            <div class="col-md-6 mb-3">
                                <label for="name_en" class="form-label">Name (English)</label>
                                <input type="text" class="form-control @error('name_en') is-invalid @enderror"
                                       id="name_en" name="name_en" value="{{ old('name_en') }}">
                                @error('name_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <!-- Category -->
                            <div class="col-md-6 mb-3">
                                <label for="medical_specialty_id" class="form-label">Specialty</label>
                                <select class="form-select" id="medical_specialty_id" name="medical_specialty_id" data-filter="specialty">
                                    <option value="">Select specialty first</option>
                                    @foreach($specialties as $specialty)
                                        <option value="{{ $specialty->id }}" {{ (string) old('medical_specialty_id', $selectedSpecialtyId ?? null) === (string) $specialty->id ? 'selected' : '' }}>
                                            {{ $specialty->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="category_id" class="form-label">Category</label>
                                <select class="form-select @error('category_id') is-invalid @enderror"
                                        id="category_id" name="category_id">
                                    <option value="">— No Category —</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name_en ?? $cat->name_ar }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <!-- Code -->
                            <div class="col-md-6 mb-3">
                                <label for="code" class="form-label">Service Code</label>
                                <input type="text" class="form-control font-monospace @error('code') is-invalid @enderror"
                                       id="code" name="code" value="{{ old('code') }}" placeholder="e.g. SVC-001">
                                @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <!-- Price -->
                            <div class="col-md-4 mb-3">
                                <label for="default_price" class="form-label">Default Price <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                    <input type="number" step="0.01" min="0"
                                           class="form-control @error('default_price') is-invalid @enderror"
                                           id="default_price" name="default_price" value="{{ old('default_price', 0) }}" required>
                                    @error('default_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <!-- Duration -->
                            <div class="col-md-4 mb-3">
                                <label for="duration_minutes" class="form-label">Duration (minutes) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-clock"></i></span>
                                    <input type="number" min="1" max="1440"
                                           class="form-control @error('duration_minutes') is-invalid @enderror"
                                           id="duration_minutes" name="duration_minutes" value="{{ old('duration_minutes', 30) }}" required>
                                    @error('duration_minutes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <!-- Sort Order -->
                            <div class="col-md-4 mb-3">
                                <label for="sort_order" class="form-label">Sort Order</label>
                                <input type="number" min="0"
                                       class="form-control @error('sort_order') is-invalid @enderror"
                                       id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}">
                                @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <!-- Description AR -->
                            <div class="col-md-6 mb-3">
                                <label for="description_ar" class="form-label">Description (Arabic)</label>
                                <textarea class="form-control @error('description_ar') is-invalid @enderror"
                                          id="description_ar" name="description_ar" rows="3">{{ old('description_ar') }}</textarea>
                                @error('description_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <!-- Description EN -->
                            <div class="col-md-6 mb-3">
                                <label for="description_en" class="form-label">Description (English)</label>
                                <textarea class="form-control @error('description_en') is-invalid @enderror"
                                          id="description_en" name="description_en" rows="3">{{ old('description_en') }}</textarea>
                                @error('description_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <!-- Toggles -->
                            <div class="col-12 mb-4">
                                <div class="d-flex gap-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_bookable"
                                               name="is_bookable" value="1"
                                               {{ old('is_bookable', '1') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_bookable">Online Bookable</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_active"
                                               name="is_active" value="1"
                                               {{ old('is_active', '1') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle me-1"></i>Create Service
                            </button>
                            <a href="{{ route('admin.services.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const specialtySelect = document.getElementById('medical_specialty_id');
    if (!specialtySelect) {
        return;
    }

    specialtySelect.addEventListener('change', function () {
        const url = new URL(window.location.href);
        if (this.value) {
            url.searchParams.set('medical_specialty_id', this.value);
        } else {
            url.searchParams.delete('medical_specialty_id');
        }
        window.location.assign(url.toString());
    });
})();
</script>
@endsection

