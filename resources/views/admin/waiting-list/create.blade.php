@extends('admin.layouts.app')

@section('title', __('Create Waiting List Request'))

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.dashboard.index') }}">{{ __('common.breadcrumb_dashboard') }}</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('admin.waiting-list.index') }}">{{ __('Waiting List') }}</a>
            </li>
            <li class="breadcrumb-item active">{{ __('Create') }}</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>{{ __('Add to Waiting List') }}</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.waiting-list.store') }}" novalidate>
                        @csrf

                        <!-- Patient Selection -->
                        <div class="mb-3">
                            <label for="patient_id" class="form-label">{{ __('Patient') }} <span class="text-danger">*</span></label>
                            <select name="patient_id" id="patient_id" class="form-select @error('patient_id') is-invalid @enderror" required>
                                <option value="">{{ __('Select a patient...') }}</option>
                                @foreach($patients ?? [] as $patient)
                                    <option value="{{ $patient->id }}" @selected(old('patient_id') == $patient->id)>
                                        {{ $patient->full_name }} - {{ $patient->phone }}
                                    </option>
                                @endforeach
                            </select>
                            @error('patient_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Specialty Selection -->
                        <div class="mb-3">
                            <label for="specialty_id" class="form-label">{{ __('Required Specialty') }} <span class="text-danger">*</span></label>
                            <select name="specialty_id" id="specialty_id" class="form-select @error('specialty_id') is-invalid @enderror" required>
                                <option value="">{{ __('Select a specialty...') }}</option>
                                @foreach($specialties ?? [] as $specialty)
                                    <option value="{{ $specialty->id }}" @selected(old('specialty_id') == $specialty->id)>
                                        {{ $specialty->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('specialty_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Preferred Date -->
                        <div class="mb-3">
                            <label for="preferred_date" class="form-label">{{ __('Preferred Appointment Date') }}</label>
                            <input type="date" name="preferred_date" id="preferred_date" class="form-control @error('preferred_date') is-invalid @enderror" value="{{ old('preferred_date') }}">
                            <small class="text-muted">{{ __('Optional - will be considered when converting to appointment') }}</small>
                            @error('preferred_date')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Priority Level -->
                        <div class="mb-3">
                            <label for="priority" class="form-label">{{ __('Priority Level') }}</label>
                            <select name="priority" id="priority" class="form-select">
                                <option value="normal" @selected(old('priority') === 'normal')>{{ __('Normal') }}</option>
                                <option value="high" @selected(old('priority') === 'high')>{{ __('High') }}</option>
                                <option value="urgent" @selected(old('priority') === 'urgent')>{{ __('Urgent') }}</option>
                            </select>
                        </div>

                        <!-- Contact Preference -->
                        <div class="mb-3">
                            <label for="contact_method" class="form-label">{{ __('Preferred Contact Method') }}</label>
                            <select name="contact_method" id="contact_method" class="form-select">
                                <option value="phone" @selected(old('contact_method') === 'phone')>{{ __('Phone') }}</option>
                                <option value="email" @selected(old('contact_method') === 'email')>{{ __('Email') }}</option>
                                <option value="sms" @selected(old('contact_method') === 'sms')>{{ __('SMS') }}</option>
                            </select>
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label for="notes" class="form-label">{{ __('Notes / Special Requests') }}</label>
                            <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="4" placeholder="{{ __('Any special conditions or requests...') }}">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check"></i> {{ __('Add to Waiting List') }}
                            </button>
                            <a href="{{ route('admin.waiting-list.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> {{ __('common.cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>{{ __('About Waiting List') }}</h6>
                </div>
                <div class="card-body small">
                    <p>{{ __('Add patients to the waiting list when:') }}</p>
                    <ul>
                        <li>{{ __('Desired appointment slot is fully booked') }}</li>
                        <li>{{ __('Patient prefers a specific doctor') }}</li>
                        <li>{{ __('Patient wants earlier appointment') }}</li>
                        <li>{{ __('Specialty slots are limited') }}</li>
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-diagram-2 me-2"></i>{{ __('Next Steps') }}</h6>
                </div>
                <div class="card-body small">
                    <ol>
                        <li>{{ __('Patient added to waiting list') }}</li>
                        <li>{{ __('System tracks position in queue') }}</li>
                        <li>{{ __('Notify patient when slot available') }}</li>
                        <li>{{ __('Convert to appointment') }}</li>
                        <li>{{ __('Patient confirmed') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.querySelector('form[novalidate]');
    if (form) {
        form.addEventListener('submit', function(event) {
            if (!this.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            this.classList.add('was-validated');
        });
    }
});
</script>
@endpush

