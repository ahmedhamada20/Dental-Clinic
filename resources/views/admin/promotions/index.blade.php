@extends('admin.layouts.app')

@section('title', __('admin.promotions.title'))

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">{{ __('admin.sidebar.dashboard') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('admin.promotions.title') }}</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="container-fluid">

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

    <!-- Header -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0"><i class="bi bi-tag me-2 text-primary"></i>{{ __('admin.promotions.title') }}</h5>
                    <small class="text-muted">{{ __('admin.promotions.subtitle') }}</small>
                </div>
                <div class="col-md-6 text-end">
                    <a href="{{ route('admin.promotions.create') }}" class="btn btn-success">
                        <i class="bi bi-plus-circle me-1"></i>{{ __('admin.promotions.new_promotion') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:5%">#</th>
                        <th>{{ __('admin.promotions.labels.title') }}</th>
                        <th>{{ __('admin.promotions.labels.code') }}</th>
                        <th>{{ __('admin.promotions.labels.type') }}</th>
                        <th class="text-center">{{ __('admin.promotions.labels.value') }}</th>
                        <th class="text-center">{{ __('admin.promotions.labels.services') }}</th>
                        <th>{{ __('admin.promotions.labels.date_range') }}</th>
                        <th class="text-center">{{ __('admin.promotions.labels.status') }}</th>
                        <th class="text-center" style="width:14%">{{ __('admin.promotions.labels.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($promotions as $promotion)
                        @php
                            $now = now();
                            $isExpired = $promotion->ends_at && $promotion->ends_at->lt($now);
                            $isUpcoming = $promotion->starts_at && $promotion->starts_at->gt($now);
                        @endphp
                        <tr class="{{ $isExpired ? 'table-light text-muted' : '' }}">
                            <td><span class="badge bg-secondary">{{ $promotion->id }}</span></td>
                            <td>
                                <strong>{{ $promotion->title_en ?? $promotion->title_ar }}</strong>
                                @if($promotion->title_ar && $promotion->title_en)
                                    <br><small class="text-muted">{{ $promotion->title_ar }}</small>
                                @endif
                            </td>
                            <td>
                                @if($promotion->code)
                                    <span class="badge bg-light text-dark border font-monospace">{{ $promotion->code }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info text-dark">
                                    {{ $promotion->promotion_type?->label() ?? $promotion->promotion_type }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($promotion->value !== null)
                                    <strong>{{ number_format($promotion->value, 2) }}</strong>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary">{{ $promotion->promotion_services_count }}</span>
                            </td>
                            <td>
                                <small>
                                    <i class="bi bi-calendar me-1"></i>
                                    {{ $promotion->starts_at?->format('d M Y') }} →
                                    {{ $promotion->ends_at?->format('d M Y') }}
                                </small>
                                @if($isExpired)
                                    <span class="badge bg-danger ms-1">{{ __('admin.promotions.state.expired') }}</span>
                                @elseif($isUpcoming)
                                    <span class="badge bg-warning text-dark ms-1">{{ __('admin.promotions.state.upcoming') }}</span>
                                @elseif($promotion->is_active)
                                    <span class="badge bg-success ms-1">{{ __('admin.promotions.state.running') }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($promotion->is_active)
                                    <span class="badge bg-success">{{ __('admin.promotions.status.active') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __('admin.promotions.status.inactive') }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.promotions.show', $promotion) }}"
                                       class="btn btn-sm btn-outline-info" title="{{ __('admin.promotions.actions.view') }}">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.promotions.edit', $promotion) }}"
                                       class="btn btn-sm btn-outline-primary" title="{{ __('admin.promotions.actions.edit') }}">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    @if($promotion->is_active)
                                        <form action="{{ route('admin.promotions.deactivate', $promotion) }}" method="POST" style="display:inline;">
                                            @csrf @method('PATCH')
                                            <button class="btn btn-sm btn-outline-warning" title="{{ __('admin.promotions.actions.deactivate') }}">
                                                <i class="bi bi-pause-circle"></i>
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.promotions.activate', $promotion) }}" method="POST" style="display:inline;">
                                            @csrf @method('PATCH')
                                            <button class="btn btn-sm btn-outline-success" title="{{ __('admin.promotions.actions.activate') }}">
                                                <i class="bi bi-play-circle"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('admin.promotions.destroy', $promotion) }}" method="POST"
                                          style="display:inline;"
                                          onsubmit="return confirm('{{ __('admin.promotions.confirm_delete') }}');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="{{ __('admin.promotions.actions.delete') }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="bi bi-inbox" style="font-size:2.5rem;color:#ccc;"></i>
                                <p class="text-muted mt-2 mb-0">{{ __('admin.promotions.no_promotions') }}</p>
                                <a href="{{ route('admin.promotions.create') }}" class="btn btn-sm btn-success mt-3">
                                    <i class="bi bi-plus-circle me-1"></i>{{ __('admin.promotions.create_first') }}
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($promotions->hasPages())
            <div class="card-footer bg-light">
                {{ $promotions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

