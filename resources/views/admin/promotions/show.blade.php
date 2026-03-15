@extends('admin.layouts.app')

@section('title', __('admin.promotions.show_title'))

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">{{ __('admin.sidebar.dashboard') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.promotions.index') }}">{{ __('admin.promotions.title') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $promotion->title_en ?? $promotion->title_ar }}</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Details -->
        <div class="col-lg-7">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-tag me-2 text-primary"></i>{{ __('admin.promotions.show_title') }}</h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.promotions.edit', $promotion) }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-pencil-square me-1"></i>{{ __('admin.promotions.actions.edit') }}
                        </a>
                        @if($promotion->is_active)
                            <form action="{{ route('admin.promotions.deactivate', $promotion) }}" method="POST" style="display:inline;">
                                @csrf @method('PATCH')
                                <button class="btn btn-sm btn-warning">
                                    <i class="bi bi-pause-circle me-1"></i>{{ __('admin.promotions.actions.deactivate') }}
                                </button>
                            </form>
                        @else
                            <form action="{{ route('admin.promotions.activate', $promotion) }}" method="POST" style="display:inline;">
                                @csrf @method('PATCH')
                                <button class="btn btn-sm btn-success">
                                    <i class="bi bi-play-circle me-1"></i>{{ __('admin.promotions.actions.activate') }}
                                </button>
                            </form>
                        @endif
                        <form action="{{ route('admin.promotions.destroy', $promotion) }}" method="POST" style="display:inline;"
                              onsubmit="return confirm('{{ __('admin.promotions.confirm_delete') }}');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger">
                                <i class="bi bi-trash me-1"></i>{{ __('admin.promotions.actions.delete') }}
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th class="text-muted" style="width:35%">{{ __('admin.promotions.fields.title_ar') }}</th>
                            <td>{{ $promotion->title_ar }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">{{ __('admin.promotions.fields.title_en') }}</th>
                            <td>{{ $promotion->title_en ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">{{ __('admin.promotions.fields.code') }}</th>
                            <td>
                                @if($promotion->code)
                                    <span class="badge bg-light text-dark border font-monospace fs-6">{{ $promotion->code }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">{{ __('admin.promotions.fields.promotion_type') }}</th>
                            <td>
                                <span class="badge bg-info text-dark">
                                    {{ $promotion->promotion_type?->label() ?? $promotion->promotion_type }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">{{ __('admin.promotions.fields.value') }}</th>
                            <td>
                                @if($promotion->value !== null)
                                    <strong>{{ number_format($promotion->value, 2) }}</strong>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">{{ __('admin.promotions.labels.date_range') }}</th>
                            <td>
                                {{ $promotion->starts_at?->format('d M Y H:i') }}
                                <span class="text-muted">→</span>
                                {{ $promotion->ends_at?->format('d M Y H:i') }}
                                @php $now = now(); @endphp
                                @if($promotion->ends_at?->lt($now))
                                    <span class="badge bg-danger ms-1">{{ __('admin.promotions.state.expired') }}</span>
                                @elseif($promotion->starts_at?->gt($now))
                                    <span class="badge bg-warning text-dark ms-1">{{ __('admin.promotions.state.upcoming') }}</span>
                                @else
                                    <span class="badge bg-success ms-1">{{ __('admin.promotions.state.running') }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">{{ __('admin.promotions.fields.applies_once') }}</th>
                            <td>{{ $promotion->applies_once ? __('admin.promotions.yes') : __('admin.promotions.no') }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">{{ __('admin.promotions.labels.status') }}</th>
                            <td>
                                @if($promotion->is_active)
                                    <span class="badge bg-success">{{ __('admin.promotions.status.active') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __('admin.promotions.status.inactive') }}</span>
                                @endif
                            </td>
                        </tr>
                        @if($promotion->notes)
                        <tr>
                            <th class="text-muted">{{ __('admin.promotions.fields.notes') }}</th>
                            <td>{{ $promotion->notes }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <!-- Assigned Services -->
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-briefcase me-2 text-success"></i>{{ __('admin.promotions.assigned_services') }}</h6>
                    <span class="badge bg-primary">{{ $promotion->services->count() }}</span>
                </div>
                <div class="card-body p-0">
                    @forelse($promotion->services as $svc)
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                            <div>
                                <strong>{{ $svc->name_en ?? $svc->name_ar }}</strong>
                                @if($svc->category)
                                    <br><small class="text-muted">{{ $svc->category->name_en ?? $svc->category->name_ar }}</small>
                                @endif
                            </div>
                            <span class="badge bg-primary">{{ number_format($svc->default_price, 2) }}</span>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-briefcase" style="font-size:1.5rem;"></i>
                            <p class="mb-0 mt-1">{{ __('admin.promotions.no_services_assigned') }}<br>
                                <small>{{ __('admin.promotions.invoice_level_help') }}</small>
                            </p>
                        </div>
                    @endforelse
                </div>
                @if($promotion->services->isNotEmpty())
                    <div class="card-footer bg-light text-end">
                        <a href="{{ route('admin.promotions.edit', $promotion) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil me-1"></i>{{ __('admin.promotions.actions.edit_assignments') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

