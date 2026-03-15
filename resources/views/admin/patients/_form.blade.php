@php
    $patient = $patient ?? new \App\Models\Patient\Patient();
    $profile = old('profile', [
        'occupation' => $patient->profile->occupation ?? '',
        'marital_status' => $patient->profile->marital_status ?? '',
        'preferred_language' => $patient->profile->preferred_language ?? '',
        'blood_group' => $patient->profile->blood_group ?? '',
        'notes' => $patient->profile->notes ?? '',
    ]);
    $medicalHistory = old('medical_history', [
        'allergies' => $patient->medicalHistory->allergies ?? '',
        'chronic_diseases' => $patient->medicalHistory->chronic_diseases ?? '',
        'current_medications' => $patient->medicalHistory->current_medications ?? '',
        'medical_notes' => $patient->medicalHistory->medical_notes ?? '',
        'dental_history' => $patient->medicalHistory->dental_history ?? '',
        'important_alerts' => $patient->medicalHistory->important_alerts ?? '',
    ]);
    $contacts = old('emergency_contacts', $patient->emergencyContacts?->map(fn ($contact) => [
        'name' => $contact->name,
        'relation' => $contact->relation,
        'phone' => $contact->phone,
        'notes' => $contact->notes,
    ])->values()->all() ?? []);

    if (empty($contacts)) {
        $contacts = array_fill(0, 2, ['name' => '', 'relation' => '', 'phone' => '', 'notes' => '']);
    }
@endphp

@if ($errors->any())
    <div class="alert alert-danger">
        <strong>{{ __('patients.form.fix_issues') }}</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">{{ __('patients.form.sections.profile_details') }}</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ __('patients.form.fields.first_name') }}</label>
                        <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name', $patient->first_name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('patients.form.fields.last_name') }}</label>
                        <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name', $patient->last_name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('patients.form.fields.phone') }}</label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $patient->phone) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('patients.form.fields.alternate_phone') }}</label>
                        <input type="text" name="alternate_phone" class="form-control @error('alternate_phone') is-invalid @enderror" value="{{ old('alternate_phone', $patient->alternate_phone) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('patients.form.fields.email') }}</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $patient->email) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ __('patients.form.fields.gender') }}</label>
                        <select name="gender" class="form-select @error('gender') is-invalid @enderror" required>
                            <option value="">{{ __('patients.form.fields.select') }}</option>
                            @foreach (['male', 'female', 'other'] as $value)
                                <option value="{{ $value }}" @selected(old('gender', $patient->gender) === $value)>{{ __('patients.form.gender_options.' . $value) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ __('patients.form.fields.date_of_birth') }}</label>
                        <input type="date" name="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror" value="{{ old('date_of_birth', optional($patient->date_of_birth)->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('patients.form.fields.city') }}</label>
                        <input type="text" name="city" class="form-control @error('city') is-invalid @enderror" value="{{ old('city', $patient->city) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('common.status') }}</label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            @foreach ($statuses as $status)
                                <option value="{{ $status->value }}" @selected(old('status', $patient->status?->value ?? null) === $status->value)>{{ method_exists($status, 'label') ? $status->label() : ucfirst($status->value) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('patients.form.fields.password') }} {{ $patient->exists ? '(' . __('patients.form.fields.optional') . ')' : '' }}</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('patients.form.fields.confirm_password') }}</label>
                        <input type="password" name="password_confirmation" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ __('patients.form.fields.address') }}</label>
                        <textarea name="address" rows="2" class="form-control @error('address') is-invalid @enderror">{{ old('address', $patient->address) }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ __('patients.form.fields.administrative_notes') }}</label>
                        <textarea name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $patient->notes) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">{{ __('patients.form.sections.extended_profile') }}</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ __('patients.form.fields.occupation') }}</label>
                        <input type="text" name="profile[occupation]" class="form-control" value="{{ $profile['occupation'] ?? '' }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('patients.form.fields.marital_status') }}</label>
                        <input type="text" name="profile[marital_status]" class="form-control" value="{{ $profile['marital_status'] ?? '' }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('patients.form.fields.preferred_language') }}</label>
                        <input type="text" name="profile[preferred_language]" class="form-control" value="{{ $profile['preferred_language'] ?? '' }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('patients.form.fields.blood_group') }}</label>
                        <input type="text" name="profile[blood_group]" class="form-control" value="{{ $profile['blood_group'] ?? '' }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ __('patients.form.fields.profile_notes') }}</label>
                        <textarea name="profile[notes]" rows="3" class="form-control">{{ $profile['notes'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">{{ __('patients.form.sections.medical_history') }}</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ __('patients.form.fields.allergies') }}</label>
                        <textarea name="medical_history[allergies]" rows="3" class="form-control">{{ $medicalHistory['allergies'] ?? '' }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('patients.form.fields.conditions') }}</label>
                        <textarea name="medical_history[chronic_diseases]" rows="3" class="form-control">{{ $medicalHistory['chronic_diseases'] ?? '' }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('patients.form.fields.current_medications') }}</label>
                        <textarea name="medical_history[current_medications]" rows="3" class="form-control">{{ $medicalHistory['current_medications'] ?? '' }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('patients.form.fields.important_alerts') }}</label>
                        <textarea name="medical_history[important_alerts]" rows="3" class="form-control">{{ $medicalHistory['important_alerts'] ?? '' }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('patients.form.fields.dental_history') }}</label>
                        <textarea name="medical_history[dental_history]" rows="3" class="form-control">{{ $medicalHistory['dental_history'] ?? '' }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('patients.form.fields.medical_notes') }}</label>
                        <textarea name="medical_history[medical_notes]" rows="3" class="form-control">{{ $medicalHistory['medical_notes'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ __('patients.form.sections.emergency_contacts') }}</h5>
                <span class="text-muted small">{{ __('patients.form.emergency_contacts_count', ['count' => count($contacts)]) }}</span>
            </div>
            <div class="card-body">
                @foreach ($contacts as $index => $contact)
                    <div class="border rounded-3 p-3 mb-3">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">{{ __('patients.form.fields.contact_name') }}</label>
                                <input type="text" name="emergency_contacts[{{ $index }}][name]" class="form-control" value="{{ $contact['name'] ?? '' }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ __('patients.form.fields.relation') }}</label>
                                <input type="text" name="emergency_contacts[{{ $index }}][relation]" class="form-control" value="{{ $contact['relation'] ?? '' }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ __('patients.form.fields.phone') }}</label>
                                <input type="text" name="emergency_contacts[{{ $index }}][phone]" class="form-control" value="{{ $contact['phone'] ?? '' }}">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">{{ __('patients.form.fields.notes') }}</label>
                                <textarea name="emergency_contacts[{{ $index }}][notes]" rows="2" class="form-control">{{ $contact['notes'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">{{ __('patients.form.sections.upload_medical_file') }}</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">{{ __('patients.form.fields.file') }}</label>
                    <input type="file" name="new_file" class="form-control @error('new_file') is-invalid @enderror">
                    <div class="form-text">{{ __('patients.form.file_help') }}</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('patients.form.fields.file_title') }}</label>
                    <input type="text" name="new_file_title" class="form-control" value="{{ old('new_file_title') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('patients.form.fields.category') }}</label>
                    <select name="new_file_category" class="form-select">
                        @foreach ($fileCategories as $value => $label)
                            <option value="{{ $value }}" @selected(old('new_file_category', 'other') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('patients.form.fields.file_notes') }}</label>
                    <textarea name="new_file_notes" rows="3" class="form-control">{{ old('new_file_notes') }}</textarea>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="new_file_visible_to_patient" name="new_file_visible_to_patient" value="1" @checked(old('new_file_visible_to_patient', true))>
                    <label class="form-check-label" for="new_file_visible_to_patient">{{ __('patients.form.fields.visible_to_patient') }}</label>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <button type="submit" class="btn btn-primary w-100 mb-2">{{ $submitLabel }}</button>
                <a href="{{ route('admin.patients.index') }}" class="btn btn-outline-secondary w-100">{{ __('common.cancel') }}</a>
            </div>
        </div>
    </div>
</div>

