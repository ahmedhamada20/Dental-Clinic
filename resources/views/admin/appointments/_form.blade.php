@php
    $selectedPatientId = old('patient_id', $appointment?->patient_id ?? request('patient_id') ?? null);
    $selectedSpecialty = old('specialty_id', $appointment?->specialty_id ?? $selectedSpecialtyId ?? null);
    $selectedDoctorId = old('doctor_id', $appointment?->doctor_id ?? null);
    $selectedServiceId = old('service_id', $appointment?->service_id ?? null);
    $selectedDate = old('appointment_date', optional($appointment?->appointment_date)->format('Y-m-d'));
    $selectedTime = old('appointment_time', $appointment?->appointment_time ?? null);
    $selectedStatus = old('status', $appointment?->status?->value ?? $appointment?->status ?? 'pending');
    $formOptionsUrl = route('admin.appointments.form-options');
@endphp

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex flex-wrap gap-2 small text-uppercase text-muted mb-3">
            <span class="badge text-bg-primary">{{ __('appointments.form.steps.specialty') }}</span>
            <span class="badge text-bg-secondary">{{ __('appointments.form.steps.doctor') }}</span>
            <span class="badge text-bg-secondary">{{ __('appointments.form.steps.service') }}</span>
            <span class="badge text-bg-secondary">{{ __('appointments.form.steps.datetime') }}</span>
            <span class="badge text-bg-secondary">{{ __('appointments.form.steps.confirm') }}</span>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">{{ __('appointments.form.fields.patient') }}</label>
                <select name="patient_id" class="form-select" required>
                    <option value="">{{ __('appointments.form.placeholders.select_patient') }}</option>
                    @foreach ($patients as $patient)
                        <option value="{{ $patient->id }}" @selected((string) $selectedPatientId === (string) $patient->id)>
                            {{ $patient->full_name ?? ('Patient #' . $patient->id) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">{{ __('appointments.form.fields.status') }}</label>
                <select name="status" class="form-select" required>
                    @foreach ($statuses as $status)
                        @php
                            $translatedStatus = __('appointments.status.' . $status->value);
                            $statusLabel = $translatedStatus === 'appointments.status.' . $status->value
                                ? $status->label()
                                : $translatedStatus;
                        @endphp
                        <option value="{{ $status->value }}" @selected($selectedStatus === $status->value)>
                            {{ $statusLabel }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">{{ __('appointments.form.fields.specialty') }}</label>
                <select
                    id="specialty_id"
                    name="specialty_id"
                    class="form-select"
                    data-form-options-url="{{ $formOptionsUrl }}"
                    required
                >
                    <option value="">{{ __('appointments.form.placeholders.select_specialty') }}</option>
                    @foreach ($specialties as $specialty)
                        <option value="{{ $specialty->id }}" @selected((string) $selectedSpecialty === (string) $specialty->id)>
                            {{ $specialty->name }}
                        </option>
                    @endforeach
                </select>
                <div class="form-text">{{ __('appointments.form.help.specialty_filters') }}</div>
            </div>

            <div class="col-md-6">
                <label class="form-label">{{ __('appointments.form.fields.doctor') }}</label>
                <select id="doctor_id" name="doctor_id" class="form-select" @disabled(!$selectedSpecialty) required>
                    <option value="">{{ $selectedSpecialty ? __('appointments.form.placeholders.select_doctor') : __('appointments.form.placeholders.select_specialty_first') }}</option>
                    @foreach ($doctors as $doctor)
                        <option value="{{ $doctor->id }}" @selected((string) $selectedDoctorId === (string) $doctor->id)>
                            {{ $doctor->display_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">{{ __('appointments.form.fields.service') }}</label>
                <select id="service_id" name="service_id" class="form-select" @disabled(!$selectedSpecialty) required>
                    <option value="">{{ $selectedSpecialty ? __('appointments.form.placeholders.select_service') : __('appointments.form.placeholders.select_specialty_first') }}</option>
                    @foreach ($services as $service)
                        <option value="{{ $service->id }}" @selected((string) $selectedServiceId === (string) $service->id)>
                            {{ $service->name_en ?: $service->name_ar }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">{{ __('appointments.form.fields.appointment_date') }}</label>
                <input type="date" name="appointment_date" class="form-control" value="{{ $selectedDate }}" required>
            </div>

            <div class="col-md-3">
                <label class="form-label">{{ __('appointments.form.fields.appointment_time') }}</label>
                <input type="time" name="appointment_time" class="form-control" value="{{ $selectedTime }}" required>
            </div>

            <div class="col-12">
                <label class="form-label">{{ __('appointments.form.fields.notes') }}</label>
                <textarea name="notes" rows="3" class="form-control">{{ old('notes', $appointment?->notes ?? null) }}</textarea>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2">
    <a href="{{ route('admin.appointments.index') }}" class="btn btn-outline-secondary">{{ __('common.cancel') }}</a>
    <button type="submit" class="btn btn-primary">{{ __('appointments.form.actions.confirm_booking') }}</button>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var specialtySelect = document.getElementById('specialty_id');
    var doctorSelect = document.getElementById('doctor_id');
    var serviceSelect = document.getElementById('service_id');

    if (!specialtySelect || !doctorSelect || !serviceSelect) {
        return;
    }

    var url = specialtySelect.getAttribute('data-form-options-url');
    var selectedDoctorId = '{{ (string) $selectedDoctorId }}';
    var selectedServiceId = '{{ (string) $selectedServiceId }}';

    function setSelectState(selectElement, placeholder, disabled) {
        selectElement.innerHTML = '';
        var option = document.createElement('option');
        option.value = '';
        option.textContent = placeholder;
        selectElement.appendChild(option);
        selectElement.disabled = disabled;
    }

    function appendOptions(selectElement, items, selectedValue) {
        items.forEach(function (item) {
            var option = document.createElement('option');
            option.value = String(item.id);
            option.textContent = item.name;
            if (selectedValue && String(item.id) === String(selectedValue)) {
                option.selected = true;
            }
            selectElement.appendChild(option);
        });
    }

    function loadOptionsBySpecialty(specialtyId) {
        if (!specialtyId) {
            setSelectState(doctorSelect, '{{ __('appointments.form.placeholders.select_specialty_first') }}', true);
            setSelectState(serviceSelect, '{{ __('appointments.form.placeholders.select_specialty_first') }}', true);
            return;
        }

        setSelectState(doctorSelect, '{{ __('appointments.form.placeholders.loading') }}', true);
        setSelectState(serviceSelect, '{{ __('appointments.form.placeholders.loading') }}', true);

        fetch(url + '?specialty_id=' + encodeURIComponent(specialtyId), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
            .then(function (response) { return response.json(); })
            .then(function (payload) {
                var doctors = Array.isArray(payload.doctors) ? payload.doctors : [];
                var services = Array.isArray(payload.services) ? payload.services : [];

                setSelectState(doctorSelect, '{{ __('appointments.form.placeholders.select_doctor') }}', false);
                setSelectState(serviceSelect, '{{ __('appointments.form.placeholders.select_service') }}', false);

                appendOptions(doctorSelect, doctors, selectedDoctorId);
                appendOptions(serviceSelect, services, selectedServiceId);

                selectedDoctorId = '';
                selectedServiceId = '';
            })
            .catch(function () {
                setSelectState(doctorSelect, '{{ __('appointments.form.placeholders.load_failed') }}', true);
                setSelectState(serviceSelect, '{{ __('appointments.form.placeholders.load_failed') }}', true);
            });
    }

    specialtySelect.addEventListener('change', function () {
        selectedDoctorId = '';
        selectedServiceId = '';
        loadOptionsBySpecialty(specialtySelect.value);
    });

    loadOptionsBySpecialty(specialtySelect.value);
});
</script>
@endpush

