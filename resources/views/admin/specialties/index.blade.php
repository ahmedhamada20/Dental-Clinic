@extends('admin.layouts.app')

@section('title', __('specialties.title'))

@section('breadcrumb')
    <nav aria-label="{{ __('admin.layout.breadcrumb_label') }}">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">{{ __('admin.sidebar.dashboard') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('specialties.title') }}</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="container-fluid">
    <form method="GET" class="card card-body mb-3">
        <div class="row g-2">
            <div class="col-md-5">
                <label class="form-label">{{ __('specialties.actions.search') }}</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="{{ __('specialties.placeholders.search') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('common.status') }}</label>
                <select name="is_active" class="form-select">
                    <option value="">{{ __('specialties.filters.all_statuses') }}</option>
                    <option value="1" @selected(request('is_active') === '1')>{{ __('specialties.status.active') }}</option>
                    <option value="0" @selected(request('is_active') === '0')>{{ __('specialties.status.inactive') }}</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-outline-primary w-100">{{ __('specialties.actions.filter') }}</button>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <a href="{{ route('admin.specialties.create') }}" class="btn btn-success w-100">{{ __('specialties.actions.new') }}</a>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('specialties.columns.id') }}</th>
                        <th>{{ __('specialties.columns.name') }}</th>
                        <th>{{ __('specialties.columns.description') }}</th>
                        <th class="text-center">{{ __('specialties.columns.doctors') }}</th>
                        <th class="text-center">{{ __('specialties.columns.categories') }}</th>
                        <th class="text-center">{{ __('specialties.columns.status') }}</th>
                        <th class="text-end">{{ __('specialties.columns.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($specialties as $specialty)
                        <tr>
                            <td>{{ $specialty->id }}</td>
                            <td><strong>{{ $specialty->name }}</strong></td>
                            <td>{{ \Illuminate\Support\Str::limit($specialty->description, 80) ?: __('common.none') }}</td>
                            <td class="text-center">{{ $specialty->doctors_count }}</td>
                            <td class="text-center">{{ $specialty->service_categories_count }}</td>
                            <td class="text-center">
                                @if ($specialty->is_active)
                                    <span class="badge bg-success">{{ __('specialties.status.active') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __('specialties.status.inactive') }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.specialties.show', $specialty) }}" class="btn btn-sm btn-outline-info">{{ __('specialties.actions.view_doctors') }}</a>
                                <a href="{{ route('admin.specialties.edit', $specialty) }}" class="btn btn-sm btn-outline-primary">{{ __('specialties.actions.edit') }}</a>
                                @if ($specialty->is_active)
                                    <form method="POST" action="{{ route('admin.specialties.deactivate', $specialty) }}" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-outline-warning">{{ __('specialties.actions.deactivate') }}</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.specialties.activate', $specialty) }}" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-outline-success">{{ __('specialties.actions.activate') }}</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-4">{{ __('specialties.messages.empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">{{ $specialties->links() }}</div>
    </div>
</div>
@endsection

