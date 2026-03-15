@extends('admin.layouts.app')

@section('title', __('users.title'))

@section('breadcrumb')
    <nav aria-label="{{ __('admin.layout.breadcrumb_label') }}">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">{{ __('common.breadcrumb_dashboard') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('users.title') }}</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="container-fluid">
    <form method="GET" class="card card-body mb-3">
        <div class="row g-2">
            <div class="col-md-3">
                <label class="form-label">{{ __('common.search') }}</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="{{ __('users.search_placeholder') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">{{ __('common.status') }}</label>
                <select name="status" class="form-select">
                    <option value="">{{ __('common.all') }}</option>
                    <option value="active" @selected(request('status') === 'active')>{{ __('common.active') }}</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>{{ __('common.inactive') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">{{ __('users.user_type') }}</label>
                <select name="user_type" class="form-select">
                    <option value="">{{ __('common.all') }}</option>
                    @foreach (\App\Enums\UserType::cases() as $type)
                        <option value="{{ $type->value }}" @selected(request('user_type') === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('users.specialty') }}</label>
                <select name="specialty_id" class="form-select">
                    <option value="">{{ __('users.all_specialties') }}</option>
                    @foreach ($specialties as $specialty)
                        <option value="{{ $specialty->id }}" @selected((string) request('specialty_id') === (string) $specialty->id)>{{ $specialty->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button class="btn btn-outline-primary w-100">{{ __('common.go') }}</button>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                @can('users.create')
                    <a href="{{ route('admin.users.create') }}" class="btn btn-success w-100">{{ __('common.new') }}</a>
                @endcan
            </div>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('users.columns.id') }}</th>
                        <th>{{ __('users.columns.name') }}</th>
                        <th>{{ __('users.columns.type') }}</th>
                        <th>{{ __('users.columns.specialty') }}</th>
                        <th>{{ __('users.columns.email') }}</th>
                        <th>{{ __('users.columns.phone') }}</th>
                        <th>{{ __('users.columns.status') }}</th>
                        <th class="text-end">{{ __('users.columns.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->display_name }}</td>
                            <td>{{ $user->user_type?->label() ?? ucfirst((string) $user->user_type) }}</td>
                            <td>{{ $user->specialty?->name ?? __('common.not_available') }}</td>
                            <td>{{ $user->email ?? __('common.not_available') }}</td>
                            <td>{{ $user->phone ?? __('common.not_available') }}</td>
                            <td>
                                {{ ($user->status?->value ?? $user->status) === 'active'
                                    ? __('common.active')
                                    : (($user->status?->value ?? $user->status) === 'inactive' ? __('common.inactive') : (string) ($user->status?->value ?? $user->status)) }}
                            </td>
                            <td class="text-end">
                                @can('users.edit')
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.users.edit', $user) }}">{{ __('common.edit') }}</a>
                                @endcan
                                @can('users.delete')
                                    <form class="d-inline" method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('{{ __('users.confirm_delete') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">{{ __('common.delete') }}</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center py-4">{{ __('users.empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">{{ $users->links() }}</div>
    </div>
</div>
@endsection

