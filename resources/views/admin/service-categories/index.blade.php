@extends('admin.layouts.app')

@section('title', __('service_categories.title'))

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Service Categories</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="container-fluid">

    <form method="GET" class="card card-body mb-3">
        <div class="row g-2">
            <div class="col-md-4">
                <label class="form-label">Specialty</label>
                <select name="medical_specialty_id" class="form-select">
                    <option value="">All specialties</option>
                    @foreach ($specialties as $specialty)
                        <option value="{{ $specialty->id }}" @selected((string) request('medical_specialty_id') === (string) $specialty->id)>{{ $specialty->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-outline-primary w-100">Filter</button>
            </div>
        </div>
    </form>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Header Card -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0"><i class="bi bi-grid me-2 text-primary"></i>Service Categories</h5>
                    <small class="text-muted">Manage the categories that group your clinic services.</small>
                </div>
                <div class="col-md-6 text-end">
                    <a href="{{ route('admin.service-categories.create') }}" class="btn btn-success">
                        <i class="bi bi-plus-circle me-2"></i>New Category
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:6%">#</th>
                        <th>Name (AR)</th>
                        <th>Name (EN)</th>
                        <th>Specialty</th>
                        <th class="text-center">Services</th>
                        <th class="text-center">Sort</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td><span class="badge bg-secondary">{{ $category->id }}</span></td>
                            <td><strong>{{ $category->name_ar }}</strong></td>
                            <td>{{ $category->name_en ?? '—' }}</td>
                            <td>{{ $category->medicalSpecialty?->name ?? '—' }}</td>
                            <td class="text-center">
                                <span class="badge bg-info">{{ $category->services_count }}</span>
                            </td>
                            <td class="text-center">{{ $category->sort_order }}</td>
                            <td class="text-center">
                                @if($category->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.service-categories.edit', $category) }}"
                                       class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>

                                    @if($category->is_active)
                                        <form action="{{ route('admin.service-categories.deactivate', $category) }}" method="POST" style="display:inline;">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-warning" title="Deactivate">
                                                <i class="bi bi-pause-circle"></i>
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.service-categories.activate', $category) }}" method="POST" style="display:inline;">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Activate">
                                                <i class="bi bi-play-circle"></i>
                                            </button>
                                        </form>
                                    @endif

                                    <form action="{{ route('admin.service-categories.destroy', $category) }}" method="POST"
                                          style="display:inline;"
                                          onsubmit="return confirm('Delete this category? This action cannot be undone.');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="bi bi-inbox" style="font-size:2.5rem;color:#ccc;"></i>
                                <p class="text-muted mt-2 mb-0">No service categories found.</p>
                                <a href="{{ route('admin.service-categories.create') }}" class="btn btn-sm btn-success mt-3">
                                    <i class="bi bi-plus-circle me-1"></i>Create First Category
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($categories->hasPages())
            <div class="card-footer bg-light">
                {{ $categories->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

