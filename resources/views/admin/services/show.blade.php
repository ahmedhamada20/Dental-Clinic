@extends('admin.layouts.app')

@section('title', __('services.details'))

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.services.index') }}">Services</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $service->name_en ?? $service->name_ar }}</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-7">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-briefcase me-2 text-primary"></i>Service Details</h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.services.edit', $service) }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-pencil-square me-1"></i>Edit
                        </a>
                        @if($service->is_active)
                            <form action="{{ route('admin.services.deactivate', $service) }}" method="POST" style="display:inline;">
                                @csrf @method('PATCH')
                                <button class="btn btn-sm btn-warning">
                                    <i class="bi bi-pause-circle me-1"></i>Deactivate
                                </button>
                            </form>
                        @else
                            <form action="{{ route('admin.services.activate', $service) }}" method="POST" style="display:inline;">
                                @csrf @method('PATCH')
                                <button class="btn btn-sm btn-success">
                                    <i class="bi bi-play-circle me-1"></i>Activate
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th class="text-muted" style="width:35%">Name (AR)</th>
                            <td>{{ $service->name_ar }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Name (EN)</th>
                            <td>{{ $service->name_en ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Code</th>
                            <td><span class="font-monospace">{{ $service->code ?? '—' }}</span></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Category</th>
                            <td>
                                @if($service->category)
                                    <span class="badge bg-light text-dark border">
                                        {{ $service->category->name_en ?? $service->category->name_ar }}
                                    </span>
                                @else
                                    <span class="text-muted">None</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">Default Price</th>
                            <td><strong class="text-primary">{{ number_format($service->default_price, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Duration</th>
                            <td>{{ $service->duration_minutes }} minutes</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Online Bookable</th>
                            <td>
                                @if($service->is_bookable)
                                    <span class="badge bg-success">Yes</span>
                                @else
                                    <span class="badge bg-secondary">No</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">Status</th>
                            <td>
                                @if($service->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">Description (AR)</th>
                            <td>{{ $service->description_ar ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Description (EN)</th>
                            <td>{{ $service->description_en ?? '—' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-tag me-2 text-success"></i>Applied Promotions</h6>
                </div>
                <div class="card-body p-0">
                    @forelse($service->promotions as $promo)
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                            <div>
                                <strong>{{ $promo->title_en ?? $promo->title_ar }}</strong>
                                <br>
                                <small class="text-muted">
                                    {{ $promo->promotion_type?->label() }} —
                                    {{ $promo->starts_at?->format('d M Y') }} to {{ $promo->ends_at?->format('d M Y') }}
                                </small>
                            </div>
                            @if($promo->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-tag" style="font-size:1.5rem;"></i>
                            <p class="mb-0 mt-1">No promotions assigned.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

