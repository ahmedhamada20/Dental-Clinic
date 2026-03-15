@extends('admin.layouts.app')

@section('title', __('admin.settings.title'))

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="mb-4">
        <h1 class="h4 mb-2">{{ __('admin.settings.title') }}</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">{{ __('common.breadcrumb_dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ __('admin.settings.title') }}</li>
            </ol>
        </nav>
    </div>

    <!-- Flash Messages -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Validation Errors -->
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h6 class="alert-heading mb-2"><i class="bi bi-exclamation-triangle"></i> {{ __('validation.fix_following') }}</h6>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- Clinic Settings -->
            <div class="col-lg-8">
                <!-- General Settings -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">{{ __('admin.settings.clinic_info') }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Clinic Name -->
                            <div class="col-12">
                                <label class="form-label">{{ __('admin.settings.clinic_name') }}<span class="text-danger">*</span></label>
                                <input type="text" name="clinic_name" class="form-control @error('clinic_name') is-invalid @enderror"
                                       value="{{ old('clinic_name', $settings['clinic_name'] ?? '') }}" required>
                                @error('clinic_name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Address -->
                            <div class="col-12">
                                <label class="form-label">{{ __('admin.settings.address') }}</label>
                                <textarea name="address" class="form-control @error('address') is-invalid @enderror"
                                          rows="2" placeholder="{{ __('Clinic address...') }}">{{ old('address', $settings['address'] ?? '') }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Phone -->
                            <div class="col-md-6">
                                <label class="form-label">{{ __('admin.settings.phone') }}</label>
                                <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                       value="{{ old('phone', $settings['phone'] ?? '') }}" placeholder="+1 (555) 123-4567">
                                @error('phone')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="col-md-6">
                                <label class="form-label">{{ __('admin.settings.email') }}</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email', $settings['email'] ?? '') }}" placeholder="clinic@example.com">
                                @error('email')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Working Hours -->
                            <div class="col-12">
                                <label class="form-label">{{ __('admin.settings.working_hours') }}</label>
                                <input type="text" name="working_hours" class="form-control @error('working_hours') is-invalid @enderror"
                                       value="{{ old('working_hours', $settings['working_hours'] ?? '') }}" placeholder="e.g., Mon-Fri: 9:00 AM - 6:00 PM">
                                @error('working_hours')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Appointment Rules -->
                            <div class="col-12">
                                <label class="form-label">{{ __('admin.settings.appointment_rules') }}</label>
                                <textarea name="appointment_rules" class="form-control @error('appointment_rules') is-invalid @enderror"
                                          rows="2" placeholder="{{ __('Appointment rules and policies...') }}">{{ old('appointment_rules', $settings['appointment_rules'] ?? '') }}</textarea>
                                @error('appointment_rules')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Preferences -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">{{ __('admin.settings.system_preferences') }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Currency -->
                            <div class="col-md-6">
                                <label class="form-label">{{ __('admin.settings.currency') }}<span class="text-danger">*</span></label>
                                <select name="currency" class="form-select @error('currency') is-invalid @enderror" required>
                                    <option value="">{{ __('Select Currency') }}</option>
                                    <option value="USD" @selected(old('currency', $settings['currency'] ?? '') === 'USD')>USD - US Dollar</option>
                                    <option value="EUR" @selected(old('currency', $settings['currency'] ?? '') === 'EUR')>EUR - Euro</option>
                                    <option value="GBP" @selected(old('currency', $settings['currency'] ?? '') === 'GBP')>GBP - British Pound</option>
                                    <option value="AED" @selected(old('currency', $settings['currency'] ?? '') === 'AED')>AED - UAE Dirham</option>
                                    <option value="SAR" @selected(old('currency', $settings['currency'] ?? '') === 'SAR')>SAR - Saudi Riyal</option>
                                    <option value="KWD" @selected(old('currency', $settings['currency'] ?? '') === 'KWD')>KWD - Kuwaiti Dinar</option>
                                    <option value="QAR" @selected(old('currency', $settings['currency'] ?? '') === 'QAR')>QAR - Qatari Rial</option>
                                </select>
                                @error('currency')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Language -->
                            <div class="col-md-6">
                                <label class="form-label">{{ __('admin.settings.language') }}<span class="text-danger">*</span></label>
                                <select name="language" class="form-select @error('language') is-invalid @enderror" required>
                                    <option value="">{{ __('Select Language') }}</option>
                                    <option value="en" @selected(old('language', $settings['language'] ?? '') === 'en')>English</option>
                                    <option value="ar" @selected(old('language', $settings['language'] ?? '') === 'ar')>العربية</option>
                                </select>
                                @error('language')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Timezone -->
                            <div class="col-md-12">
                                <label class="form-label">{{ __('admin.settings.timezone') }}<span class="text-danger">*</span></label>
                                <select name="timezone" class="form-select @error('timezone') is-invalid @enderror" required>
                                    @foreach (timezone_abbreviations_list() as $abbr => $tzs)
                                        @foreach ($tzs as $tz)
                                            <option value="{{ $tz['timezone_id'] }}" @selected(old('timezone', $settings['timezone'] ?? 'UTC') === $tz['timezone_id'])>
                                                {{ $tz['timezone_id'] }}
                                            </option>
                                        @endforeach
                                    @endforeach
                                </select>
                                @error('timezone')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> {{ __('admin.save') }}
                    </button>
                    <a href="{{ route('admin.dashboard.index') }}" class="btn btn-secondary">
                        {{ __('admin.cancel') }}
                    </a>
                </div>
            </div>

            <!-- Clinic Logo -->
            <div class="col-lg-4">
                <div class="card sticky-top" style="top: 20px;">
                    <div class="card-header">
                        <h6 class="mb-0">{{ __('admin.settings.clinic_logo') }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            @if (!empty($settings['clinic_logo']) && file_exists(public_path('storage/' . $settings['clinic_logo'])))
                                <img src="{{ asset('storage/' . $settings['clinic_logo']) }}" alt="Clinic Logo" class="img-fluid rounded mb-3" style="max-height: 200px;">
                            @else
                                <div class="bg-light rounded p-5 text-center mb-3">
                                    <i class="bi bi-image" style="font-size: 3rem; color: #ccc;"></i>
                                    <p class="text-muted mt-2">{{ __('No logo uploaded') }}</p>
                                </div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('admin.settings.clinic_logo') }}</label>
                            <input type="file" name="clinic_logo" class="form-control @error('clinic_logo') is-invalid @enderror"
                                   accept="image/*">
                            <small class="text-muted">
                                {{ __('Supported formats: JPG, PNG, SVG, WebP. Max 2MB.') }}
                            </small>
                            @error('clinic_logo')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        @if (!empty($settings['clinic_logo']))
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="removeLogo" name="remove_logo">
                                <label class="form-check-label" for="removeLogo">
                                    {{ __('Remove current logo') }}
                                </label>
                            </div>
                        @endif
                    </div>
                    <div class="card-footer">
                        <small class="text-muted">
                            {{ __('Updated at') }}: {{ now()->format('Y-m-d H:i') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

