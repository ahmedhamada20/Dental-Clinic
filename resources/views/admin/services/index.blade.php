@extends('admin.layouts.app')

@section('title', __('services.title'))

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Services</li>
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
                    <h5 class="mb-0"><i class="bi bi-briefcase me-2 text-primary"></i>Clinic Services</h5>
                    <small class="text-muted">Manage all services offered at the clinic.</small>
                </div>
                <div class="col-md-6 text-end d-flex gap-2 justify-content-end">
                    <a href="{{ route('admin.service-categories.index') }}" class="btn btn-outline-primary">
                        <i class="bi bi-grid me-1"></i>Categories
                    </a>
                    <a href="{{ route('admin.services.create') }}" class="btn btn-success">
                        <i class="bi bi-plus-circle me-1"></i>New Service
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
                        <th style="width:5%">#</th>
                        <th>Name (AR)</th>
                        <th>Name (EN)</th>
                        <th>Category</th>
                        <th>Specialty</th>
                        <th class="text-center">Price</th>
                        <th class="text-center">Duration</th>
                        <th class="text-center">Bookable</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" style="width:14%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($services as $service)
                        <tr>
                            <td><span class="badge bg-secondary">{{ $service->id }}</span></td>
                            <td>
                                <strong>{{ $service->name_ar }}</strong>
                                @if($service->code)
                                    <br><small class="text-muted font-monospace">{{ $service->code }}</small>
                                @endif
                            </td>
                            <td>{{ $service->name_en ?? '—' }}</td>
                            <td>
                                @if($service->category)
                                    <span class="badge bg-light text-dark border">{{ $service->category->name_en ?? $service->category->name_ar }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $service->category?->medicalSpecialty?->name ?? '—' }}</td>
                            <td class="text-center">
                                <span class="badge bg-primary">{{ number_format($service->default_price, 2) }}</span>
                            </td>
                            <td class="text-center">
                                <span class="text-muted">{{ $service->duration_minutes }} min</span>
                            </td>
                            <td class="text-center">
                                @if($service->is_bookable)
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                @else
                                    <i class="bi bi-x-circle-fill text-danger"></i>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($service->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.services.show', $service) }}"
                                       class="btn btn-sm btn-outline-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.services.edit', $service) }}"
                                       class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    @if($service->is_active)
                                        <form action="{{ route('admin.services.deactivate', $service) }}" method="POST" style="display:inline;">
                                            @csrf @method('PATCH')
                                            <button class="btn btn-sm btn-outline-warning" title="Deactivate">
                                                <i class="bi bi-pause-circle"></i>
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.services.activate', $service) }}" method="POST" style="display:inline;">
                                            @csrf @method('PATCH')
                                            <button class="btn btn-sm btn-outline-success" title="Activate">
                                                <i class="bi bi-play-circle"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('admin.services.destroy', $service) }}" method="POST"
                                          style="display:inline;"
                                          onsubmit="return confirm('Delete this service? This cannot be undone.');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <i class="bi bi-inbox" style="font-size:2.5rem;color:#ccc;"></i>
                                <p class="text-muted mt-2 mb-0">No services found.</p>
                                <a href="{{ route('admin.services.create') }}" class="btn btn-sm btn-success mt-3">
                                    <i class="bi bi-plus-circle me-1"></i>Create First Service
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($services->hasPages())
            <div class="card-footer bg-light">
                {{ $services->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

