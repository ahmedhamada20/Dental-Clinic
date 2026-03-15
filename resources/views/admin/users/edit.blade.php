@extends('admin.layouts.app')

@section('title', __('users.edit_title'))

@section('breadcrumb')
    <nav aria-label="{{ __('admin.layout.breadcrumb_label') }}">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">{{ __('common.breadcrumb_dashboard') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">{{ __('users.title') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('common.edit') }}</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white">{{ __('users.edit_title') }}</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.users.update', $user) }}">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('users.fields.first_name') }}</label>
                                <input name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name', $user->first_name) }}" required>
                                @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('users.fields.last_name') }}</label>
                                <input name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name', $user->last_name) }}" required>
                                @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('users.columns.email') }}</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('users.columns.phone') }}</label>
                                <input name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}" required>
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ __('users.user_type') }}</label>
                                <select name="user_type" id="user_type" class="form-select @error('user_type') is-invalid @enderror" required>
                                    @foreach ($userTypes as $type)
                                        <option value="{{ $type->value }}" @selected(old('user_type', $user->user_type?->value ?? $user->user_type) === $type->value)>{{ $type->label() }}</option>
                                    @endforeach
                                </select>
                                @error('user_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ __('common.status') }}</label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status->value }}" @selected(old('status', $user->status?->value ?? $user->status) === $status->value)>{{ ucfirst($status->value) }}</option>
                                    @endforeach
                                </select>
                                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4" id="specialty_wrapper">
                                <label class="form-label">{{ __('users.specialty') }}</label>
                                <select name="specialty_id" class="form-select @error('specialty_id') is-invalid @enderror">
                                    <option value="">{{ __('users.fields.select_specialty') }}</option>
                                    @foreach ($specialties as $specialty)
                                        <option value={{ $specialty->id }}>{{ $specialty->name }}</option>
                                    @endforeach
                                </select>
                                @error('specialty_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('users.fields.new_password_optional') }}</label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('users.fields.confirm_new_password') }}</label>
                                <input type="password" name="password_confirmation" class="form-control">
                            </div>
                        </div>
                        <div class="d-flex gap-2 mt-4">
                            <button class="btn btn-primary">{{ __('users.actions.save_changes') }}</button>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">{{ __('common.cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection

