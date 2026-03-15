@php
    $selectedPatientId = old('patient_id', $prescription->patient_id ?? request('patient_id'));
    $selectedVisitId = old('visit_id', $prescription->visit_id ?? request('visit_id'));
    $selectedDoctorId = old('doctor_id', $prescription->doctor_id ?? auth()->id());
    $selectedIssuedAt = old('issued_at', optional($prescription->issued_at ?? now())->format('Y-m-d\TH:i'));

    $rows = old('items');
    if (!is_array($rows) || $rows === []) {
        $rows = isset($prescription) && $prescription->relationLoaded('items')
            ? $prescription->items->map(fn ($item) => [
                'medicine_name' => $item->medicine_name,
                'dosage' => $item->dosage,
                'frequency' => $item->frequency,
                'dose_duration' => $item->dose_duration,
                'treatment_duration' => $item->treatment_duration ?: $item->duration,
                'instructions' => $item->instructions,
            ])->all()
            : [];
    }

    if ($rows === []) {
        $rows = [[
            'medicine_name' => '',
            'dosage' => '',
            'frequency' => '',
            'dose_duration' => '',
            'treatment_duration' => '',
            'instructions' => '',
        ]];
    }

    $visitsEndpoint = route('admin.prescriptions.patients.visits', ['patient' => '__PATIENT__']);
@endphp

@if ($errors->any())
    <div class="alert alert-danger">
        <strong>{{ __('admin.validation_errors') }}</strong>
        <ul class="mb-0 mt-2 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card mb-3">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">{{ __('admin.prescriptions.patient_name') }} <span class="text-danger">*</span></label>
                <select id="prescription_patient_id" name="patient_id" class="form-select" required>
                    <option value="">{{ __('admin.billing.select_patient') }}</option>
                    @foreach ($patients as $patient)
                        <option value="{{ $patient->id }}" @selected((string) $selectedPatientId === (string) $patient->id)>
                            {{ $patient->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">{{ __('admin.prescriptions.visit_no') }} <span class="text-danger">*</span></label>
                <select id="prescription_visit_id" name="visit_id" class="form-select" @disabled(!$selectedPatientId) required>
                    <option value="">{{ __('admin.billing.select_visit') }}</option>
                    @foreach ($visits as $visit)
                        <option value="{{ $visit->id }}" @selected((string) $selectedVisitId === (string) $visit->id)>
                            {{ $visit->visit_no }} - {{ optional($visit->visit_date)->format('Y-m-d') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">{{ __('admin.prescriptions.doctor_name') }} <span class="text-danger">*</span></label>
                <select name="doctor_id" class="form-select" required>
                    <option value="">{{ __('appointments.form.placeholders.select_doctor') }}</option>
                    @foreach ($doctors as $doctor)
                        <option value="{{ $doctor->id }}" @selected((string) $selectedDoctorId === (string) $doctor->id)>
                            {{ $doctor->display_name ?? $doctor->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">{{ __('admin.prescriptions.issued_date') }}</label>
                <input type="datetime-local" name="issued_at" class="form-control" value="{{ $selectedIssuedAt }}">
            </div>

            <div class="col-12">
                <label class="form-label">{{ __('admin.prescriptions.notes') }}</label>
                <textarea name="notes" rows="2" class="form-control">{{ old('notes', $prescription->notes ?? '') }}</textarea>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">{{ __('admin.prescriptions.medicines') }}</h6>
        <button type="button" id="add-prescription-item" class="btn btn-sm btn-outline-primary">+ {{ __('admin.prescriptions.add_item') }}</button>
    </div>
    <div class="card-body">
        <div id="prescription-items">
            @foreach ($rows as $index => $row)
                <div class="border rounded p-3 mb-3 prescription-item-row" data-index="{{ $index }}">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label">{{ __('admin.prescriptions.medicine_name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="items[{{ $index }}][medicine_name]" class="form-control" value="{{ $row['medicine_name'] ?? '' }}" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">{{ __('admin.prescriptions.dosage') }}</label>
                            <input type="text" name="items[{{ $index }}][dosage]" class="form-control" value="{{ $row['dosage'] ?? '' }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">{{ __('admin.prescriptions.frequency') }}</label>
                            <input type="text" name="items[{{ $index }}][frequency]" class="form-control" value="{{ $row['frequency'] ?? '' }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">{{ __('admin.prescriptions.dose_duration') }}</label>
                            <input type="text" name="items[{{ $index }}][dose_duration]" class="form-control" value="{{ $row['dose_duration'] ?? '' }}" placeholder="e.g. 7 days">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">{{ __('admin.prescriptions.treatment_duration') }}</label>
                            <input type="text" name="items[{{ $index }}][treatment_duration]" class="form-control" value="{{ $row['treatment_duration'] ?? '' }}" placeholder="e.g. 30 days">
                        </div>
                        <div class="col-md-10">
                            <label class="form-label">{{ __('admin.prescriptions.instructions') }}</label>
                            <input type="text" name="items[{{ $index }}][instructions]" class="form-control" value="{{ $row['instructions'] ?? '' }}">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-danger w-100 remove-prescription-item">{{ __('admin.delete') }}</button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary">{{ __('admin.save') }}</button>
    <a href="{{ route('admin.prescriptions.index') }}" class="btn btn-secondary">{{ __('admin.cancel') }}</a>
</div>

<template id="prescription-item-template">
    <div class="border rounded p-3 mb-3 prescription-item-row" data-index="__INDEX__">
        <div class="row g-2">
            <div class="col-md-4">
                <label class="form-label">{{ __('admin.prescriptions.medicine_name') }} <span class="text-danger">*</span></label>
                <input type="text" name="items[__INDEX__][medicine_name]" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">{{ __('admin.prescriptions.dosage') }}</label>
                <input type="text" name="items[__INDEX__][dosage]" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">{{ __('admin.prescriptions.frequency') }}</label>
                <input type="text" name="items[__INDEX__][frequency]" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">{{ __('admin.prescriptions.dose_duration') }}</label>
                <input type="text" name="items[__INDEX__][dose_duration]" class="form-control" placeholder="e.g. 7 days">
            </div>
            <div class="col-md-2">
                <label class="form-label">{{ __('admin.prescriptions.treatment_duration') }}</label>
                <input type="text" name="items[__INDEX__][treatment_duration]" class="form-control" placeholder="e.g. 30 days">
            </div>
            <div class="col-md-10">
                <label class="form-label">{{ __('admin.prescriptions.instructions') }}</label>
                <input type="text" name="items[__INDEX__][instructions]" class="form-control">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-outline-danger w-100 remove-prescription-item">{{ __('admin.delete') }}</button>
            </div>
        </div>
    </div>
</template>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var patientSelect = document.getElementById('prescription_patient_id');
    var visitSelect = document.getElementById('prescription_visit_id');
    var addButton = document.getElementById('add-prescription-item');
    var itemsContainer = document.getElementById('prescription-items');
    var itemTemplate = document.getElementById('prescription-item-template');
    var visitsEndpoint = @json($visitsEndpoint);
    var selectedVisitId = @json((string) $selectedVisitId);

    function nextIndex() {
        return itemsContainer.querySelectorAll('.prescription-item-row').length;
    }

    function bindRemoveButtons() {
        itemsContainer.querySelectorAll('.remove-prescription-item').forEach(function (button) {
            button.onclick = function () {
                var rows = itemsContainer.querySelectorAll('.prescription-item-row');
                if (rows.length <= 1) {
                    return;
                }
                button.closest('.prescription-item-row').remove();
            };
        });
    }

    addButton.addEventListener('click', function () {
        var html = itemTemplate.innerHTML.replaceAll('__INDEX__', String(nextIndex()));
        var wrapper = document.createElement('div');
        wrapper.innerHTML = html.trim();
        itemsContainer.appendChild(wrapper.firstElementChild);
        bindRemoveButtons();
    });

    bindRemoveButtons();

    function resetVisits(placeholder, disabled) {
        visitSelect.innerHTML = '';
        var option = document.createElement('option');
        option.value = '';
        option.textContent = placeholder;
        visitSelect.appendChild(option);
        visitSelect.disabled = disabled;
    }

    function loadVisits(patientId) {
        if (!patientId) {
            selectedVisitId = '';
            resetVisits(@json(__('admin.billing.select_visit')), true);
            return;
        }

        resetVisits(@json(__('appointments.form.placeholders.loading')), true);

        fetch(visitsEndpoint.replace('__PATIENT__', encodeURIComponent(patientId)), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
            .then(function (response) {
                if (!response.ok) throw new Error('failed');
                return response.json();
            })
            .then(function (payload) {
                var visits = Array.isArray(payload.visits) ? payload.visits : [];
                resetVisits(@json(__('admin.billing.select_visit')), false);

                visits.forEach(function (visit) {
                    var option = document.createElement('option');
                    option.value = String(visit.id);
                    option.textContent = (visit.visit_no || ('Visit #' + visit.id)) + (visit.visit_date ? (' - ' + visit.visit_date) : '');
                    if (selectedVisitId && String(visit.id) === String(selectedVisitId)) {
                        option.selected = true;
                    }
                    visitSelect.appendChild(option);
                });

                selectedVisitId = '';
            })
            .catch(function () {
                resetVisits(@json(__('appointments.form.placeholders.load_failed')), true);
            });
    }

    patientSelect.addEventListener('change', function () {
        selectedVisitId = '';
        loadVisits(patientSelect.value);
    });

    if (!patientSelect.value) {
        resetVisits(@json(__('admin.billing.select_visit')), true);
    }
});
</script>
@endpush

