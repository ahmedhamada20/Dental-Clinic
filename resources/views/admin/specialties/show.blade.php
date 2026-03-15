@extends('admin.layouts.app')

@section('title', __('specialties.show_title'))

@section('breadcrumb')
    <nav aria-label="{{ __('admin.layout.breadcrumb_label') }}">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">{{ __('admin.sidebar.dashboard') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.specialties.index') }}">{{ __('specialties.title') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $specialty->name }}</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card mb-3">
        <div class="card-body d-flex flex-wrap justify-content-between gap-3">
            <div>
                <h5 class="mb-1">{{ $specialty->name }}</h5>
                <p class="text-muted mb-0">{{ $specialty->description ?: __('common.none') }}</p>
            </div>
            <div class="d-flex gap-3">
                <div>
                    <div class="small text-muted">{{ __('specialties.columns.doctors') }}</div>
                    <strong>{{ $specialty->doctors_count }}</strong>
                </div>
                <div>
                    <div class="small text-muted">{{ __('specialties.columns.categories') }}</div>
                    <strong>{{ $specialty->service_categories_count }}</strong>
                </div>
                <div>
                    <div class="small text-muted">{{ __('specialties.columns.status') }}</div>
                    <strong>{{ $specialty->is_active ? __('specialties.status.active') : __('specialties.status.inactive') }}</strong>
                </div>
            </div>
        </div>
    </div>

    @can('specialties.manage')
        <div class="card mb-3">
            <div class="card-header">{{ __('specialties.doctors.assign_title') }}</div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.specialties.doctors.attach', $specialty) }}" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-md-8">
                        <label class="form-label">{{ __('specialties.doctors.select_doctor') }}</label>
                        <select name="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror" required>
                            <option value="">{{ __('specialties.doctors.select_doctor_placeholder') }}</option>
                            @foreach ($availableDoctors as $doctor)
                                <option value="{{ $doctor->id }}" @selected((string) old('doctor_id') === (string) $doctor->id)>
                                    {{ $doctor->display_name }}
                                    @if($doctor->specialty)
                                        - {{ __('specialties.doctors.current_specialty', ['name' => $doctor->specialty->name]) }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('doctor_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 d-grid">
                        <button type="submit" class="btn btn-primary">{{ __('specialties.actions.add_doctor') }}</button>
                    </div>
                </form>
                @if ($availableDoctors->isEmpty())
                    <div class="text-muted mt-2">{{ __('specialties.doctors.no_available_doctors') }}</div>
                @endif
            </div>
        </div>
    @endcan

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>{{ __('specialties.doctors.list_title') }}</span>
            <a href="{{ route('admin.specialties.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('specialties.actions.back_to_list') }}</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('specialties.doctors.columns.name') }}</th>
                        <th>{{ __('specialties.doctors.columns.email') }}</th>
                        <th>{{ __('specialties.doctors.columns.phone') }}</th>
                        <th>{{ __('specialties.doctors.columns.status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($specialty->doctors as $doctor)
                        <tr>
                            <td>{{ $doctor->display_name }}</td>
                            <td>{{ $doctor->email ?: __('common.not_available') }}</td>
                            <td>{{ $doctor->phone ?: __('common.not_available') }}</td>
                            <td>
                                {{ ($doctor->status?->value ?? $doctor->status) === 'active'
                                    ? __('common.active')
                                    : __('common.inactive') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4">{{ __('specialties.doctors.empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

