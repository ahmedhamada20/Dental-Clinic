@php
    $patients = $patients ?? collect();
    $doctors = $doctors ?? collect();
    $appointments = $appointments ?? collect();
    $selectedPatientId = old('patient_id', $visit->patient_id ?? request('patient_id') ?? '');
    $selectedAppointmentId = old('appointment_id', $visit->appointment_id ?? '');
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label for="visit_no" class="form-label">{{ __('visits.visit_no') }} <span class="text-danger">*</span></label>
        @if (isset($visit))
            <input
                type="text"
                id="visit_no"
                name="visit_no"
                class="form-control @error('visit_no') is-invalid @enderror"
                value="{{ old('visit_no', $visit->visit_no) }}"
                readonly
            >
        @else
            <input
                type="text"
                id="visit_no"
                class="form-control"
                value="Auto-generated after selecting doctor"
                readonly
            >
        @endif
        @error('visit_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="visit_date" class="form-label">{{ __('visits.visit_date') }} <span class="text-danger">*</span></label>
        <input
            type="date"
            id="visit_date"
            name="visit_date"
            class="form-control @error('visit_date') is-invalid @enderror"
            value="{{ old('visit_date', isset($visit->visit_date) ? $visit->visit_date->format('Y-m-d') : '') }}"
            required
        >
        @error('visit_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="appointment_id" class="form-label">{{ __('visits.appointment_label') }}</label>
        <select id="appointment_id" name="appointment_id" class="form-select @error('appointment_id') is-invalid @enderror">
            <option value="">{{ __('common.none') }}</option>
            @foreach ($appointments as $appointment)
                <option
                    value="{{ $appointment->id }}"
                    data-appointment-option="1"
                    data-patient-id="{{ $appointment->patient_id }}"
                    @selected((string) $selectedAppointmentId === (string) $appointment->id)
                >
                    {{ $appointment->appointment_no ?? ('Appointment #' . $appointment->id) }}
                </option>
            @endforeach
        </select>
        @error('appointment_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="patient_id" class="form-label">{{ __('visits.patient') }} <span class="text-danger">*</span></label>
        <select id="patient_id" name="patient_id" class="form-select @error('patient_id') is-invalid @enderror" required>
            <option value="">{{ __('visits.select_patient') }}</option>
            @foreach ($patients as $patient)
                <option value="{{ $patient->id }}" @selected((string) $selectedPatientId === (string) $patient->id)>
                    {{ $patient->full_name ?? $patient->display_name ?? $patient->name ?? ('Patient #' . $patient->id) }}
                </option>
            @endforeach
        </select>
        @error('patient_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="doctor_id" class="form-label">{{ __('visits.doctor') }} <span class="text-danger">*</span></label>
        <select id="doctor_id" name="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror" required>
            <option value="">{{ __('visits.select_doctor') }}</option>
            @foreach ($doctors as $doctor)
                <option value="{{ $doctor->id }}" @selected((string) old('doctor_id', $visit->doctor_id ?? '') === (string) $doctor->id)>
                    {{ $doctor->display_name ?? $doctor->full_name ?? ('Doctor #' . $doctor->id) }}
                </option>
            @endforeach
        </select>
        @error('doctor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="status" class="form-label">{{ __('visits.status') }} <span class="text-danger">*</span></label>
        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
            @php
                $selectedStatus = old('status', $visit->status?->value ?? (string) ($visit->status ?? 'scheduled'));
                $statusOptions = [
                    'scheduled' => __('appointments.status.scheduled'),
                    'in_progress' => __('appointments.status.in_progress'),
                    'completed' => __('appointments.status.completed'),
                    'cancelled' => __('appointments.status.cancelled'),
                    'no_show' => __('appointments.status.no_show'),
                ];
            @endphp
            @foreach ($statusOptions as $statusValue => $statusLabel)
                <option value="{{ $statusValue }}" @selected($selectedStatus === $statusValue)>{{ $statusLabel }}</option>
            @endforeach
        </select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="start_at" class="form-label">{{ __('visits.start_at') }}</label>
        <input
            type="datetime-local"
            id="start_at"
            name="start_at"
            class="form-control @error('start_at') is-invalid @enderror"
            value="{{ old('start_at', isset($visit->start_at) ? $visit->start_at->format('Y-m-d\\TH:i') : '') }}"
        >
        @error('start_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="end_at" class="form-label">{{ __('visits.end_at') }}</label>
        <input
            type="datetime-local"
            id="end_at"
            name="end_at"
            class="form-control @error('end_at') is-invalid @enderror"
            value="{{ old('end_at', isset($visit->end_at) ? $visit->end_at->format('Y-m-d\\TH:i') : '') }}"
        >
        @error('end_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label for="chief_complaint" class="form-label">{{ __('visits.complaints') }}</label>
        <textarea id="chief_complaint" name="chief_complaint" rows="2" class="form-control @error('chief_complaint') is-invalid @enderror">{{ old('chief_complaint', $visit->chief_complaint ?? '') }}</textarea>
        @error('chief_complaint')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label for="diagnosis" class="form-label">{{ __('visits.diagnosis') }}</label>
        <textarea id="diagnosis" name="diagnosis" rows="2" class="form-control @error('diagnosis') is-invalid @enderror">{{ old('diagnosis', $visit->diagnosis ?? '') }}</textarea>
        @error('diagnosis')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label for="clinical_notes" class="form-label">{{ __('visits.clinical_notes') }}</label>
        <textarea id="clinical_notes" name="clinical_notes" rows="3" class="form-control @error('clinical_notes') is-invalid @enderror">{{ old('clinical_notes', $visit->clinical_notes ?? '') }}</textarea>
        @error('clinical_notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label for="internal_notes" class="form-label">{{ __('visits.internal_notes') }}</label>
        <textarea id="internal_notes" name="internal_notes" rows="3" class="form-control @error('internal_notes') is-invalid @enderror">{{ old('internal_notes', $visit->internal_notes ?? '') }}</textarea>
        @error('internal_notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var patientSelect = document.getElementById('patient_id');
    var appointmentSelect = document.getElementById('appointment_id');

    if (!patientSelect || !appointmentSelect) {
        return;
    }

    var appointmentOptions = Array.prototype.slice.call(
        appointmentSelect.querySelectorAll('option[data-appointment-option]')
    );

    function filterAppointmentsByPatient() {
        var selectedPatientId = patientSelect.value;
        var hasSelectedPatient = selectedPatientId !== '';

        appointmentOptions.forEach(function (option) {
            var isMatchingPatient = option.getAttribute('data-patient-id') === selectedPatientId;
            option.hidden = !hasSelectedPatient || !isMatchingPatient;

            if (option.hidden && option.selected) {
                appointmentSelect.value = '';
            }
        });
    }

    patientSelect.addEventListener('change', filterAppointmentsByPatient);
    filterAppointmentsByPatient();
});
</script>

