@extends('admin.layouts.app')

@section('title', __('patients.show'))

@section('content')
@php
    $tabs = [
        'overview' => __('patients.show_page.tabs.overview'),
        'history' => __('patients.show_page.tabs.history'),
        'contacts' => __('patients.show_page.tabs.contacts'),
        'files' => __('patients.show_page.tabs.files'),
        'timeline' => __('patients.show_page.tabs.timeline'),
    ];
    $activeTab = array_key_exists($tab, $tabs) ? $tab : 'overview';
    $genderKey = in_array(strtolower((string) $patient->gender), ['male', 'female', 'other'], true)
        ? strtolower((string) $patient->gender)
        : null;
@endphp

<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">{{ $patient->display_name }}</h1>
            <div class="text-muted">{{ $patient->patient_code }} • {{ $genderKey ? __('patients.form.gender_options.' . $genderKey) : __('common.not_available') }} • {{ $patient->date_of_birth?->format('M d, Y') }} @if($patient->age) • {{ __('patients.show_page.years_old', ['age' => $patient->age]) }} @endif</div>
        </div>
        <div class="d-flex gap-2">
            @can('appointments.create')
                <a href="{{ route('admin.appointments.create', ['patient_id' => $patient->id]) }}" class="btn btn-success">{{ __('patients.show_page.actions.schedule_appointment') }}</a>
            @endcan
            @can('visits.create')
                <a href="{{ route('admin.visits.create', ['patient_id' => $patient->id]) }}" class="btn btn-outline-success">{{ __('patients.show_page.actions.create_visit') }}</a>
            @endcan
            <a href="{{ route('admin.patients.edit', $patient) }}" class="btn btn-primary">{{ __('patients.actions.edit_record') }}</a>
            <a href="{{ route('admin.patients.index') }}" class="btn btn-outline-secondary">{{ __('patients.actions.back_to_patients') }}</a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-2 col-6"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted small">{{ __('patients.show_page.stats.appointments') }}</div><div class="fs-4 fw-bold">{{ $stats['appointments'] }}</div></div></div></div>
        <div class="col-md-2 col-6"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted small">{{ __('patients.show_page.stats.visits') }}</div><div class="fs-4 fw-bold">{{ $stats['visits'] }}</div></div></div></div>
        <div class="col-md-2 col-6"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted small">{{ __('patients.show_page.stats.prescriptions') }}</div><div class="fs-4 fw-bold">{{ $stats['prescriptions'] }}</div></div></div></div>
        <div class="col-md-2 col-6"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted small">{{ __('patients.show_page.stats.invoices') }}</div><div class="fs-4 fw-bold">{{ $stats['invoices'] }}</div></div></div></div>
        <div class="col-md-2 col-6"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted small">{{ __('patients.show_page.stats.files') }}</div><div class="fs-4 fw-bold">{{ $stats['files'] }}</div></div></div></div>
        <div class="col-md-2 col-6"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted small">{{ __('common.status') }}</div><div class="fw-bold {{ $patient->status?->value === 'active' ? 'text-success' : 'text-secondary' }}">{{ $patient->status?->label() ?? __('common.not_available') }}</div></div></div></div>
    </div>

    <ul class="nav nav-pills mb-4 gap-2 flex-wrap">
        @foreach ($tabs as $key => $label)
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === $key ? 'active' : '' }}" href="{{ route('admin.patients.show', ['patient' => $patient->id, 'tab' => $key]) }}">{{ $label }}</a>
            </li>
        @endforeach
    </ul>

    @if ($activeTab === 'overview')
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white"><h5 class="mb-0">{{ __('patients.show_page.sections.profile_details') }}</h5></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6"><div class="text-muted small">{{ __('patients.form.fields.phone') }}</div><div>{{ $patient->phone }}</div></div>
                            <div class="col-md-6"><div class="text-muted small">{{ __('patients.form.fields.alternate_phone') }}</div><div>{{ $patient->alternate_phone ?: __('common.none') }}</div></div>
                            <div class="col-md-6"><div class="text-muted small">{{ __('patients.form.fields.email') }}</div><div>{{ $patient->email ?: __('common.none') }}</div></div>
                            <div class="col-md-6"><div class="text-muted small">{{ __('patients.form.fields.city') }}</div><div>{{ $patient->city ?: __('common.none') }}</div></div>
                            <div class="col-md-6"><div class="text-muted small">{{ __('patients.form.fields.occupation') }}</div><div>{{ $patient->profile?->occupation ?: __('common.none') }}</div></div>
                            <div class="col-md-6"><div class="text-muted small">{{ __('patients.form.fields.blood_group') }}</div><div>{{ $patient->profile?->blood_group ?: __('common.none') }}</div></div>
                            <div class="col-md-6"><div class="text-muted small">{{ __('patients.form.fields.preferred_language') }}</div><div>{{ $patient->profile?->preferred_language ?: __('common.none') }}</div></div>
                            <div class="col-md-6"><div class="text-muted small">{{ __('patients.form.fields.marital_status') }}</div><div>{{ $patient->profile?->marital_status ?: __('common.none') }}</div></div>
                            <div class="col-12"><div class="text-muted small">{{ __('patients.form.fields.address') }}</div><div>{{ $patient->address ?: __('common.none') }}</div></div>
                            <div class="col-12"><div class="text-muted small">{{ __('patients.form.fields.notes') }}</div><div>{{ $patient->notes ?: ($patient->profile?->notes ?: __('common.none')) }}</div></div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ __('patients.show_page.sections.all_appointments') }}</h5>
                        <span class="badge bg-light text-dark">{{ $allAppointments->total() }}</span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ __('patients.show_page.labels.appointment_no') }}</th>
                                        <th>{{ __('patients.show_page.labels.date') }}</th>
                                        <th>{{ __('common.status') }}</th>
                                        <th>{{ __('patients.show_page.labels.doctor') }}</th>
                                        <th class="text-end">{{ __('common.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($allAppointments as $appointment)
                                        <tr>
                                            <td>{{ $appointment->appointment_no ?? ('#' . $appointment->id) }}</td>
                                            <td>{{ $appointment->appointment_date?->format('M d, Y') ?? __('common.not_available') }}</td>
                                            <td>{{ $appointment->status?->label() ?? ucfirst((string) $appointment->status?->value) }}</td>
                                            <td>{{ $appointment->doctor?->display_name ?? $appointment->doctor?->full_name ?? __('common.not_available') }}</td>
                                            <td class="text-end">
                                                @can('appointments.view')
                                                    <a href="{{ route('admin.appointments.show', $appointment) }}" class="btn btn-sm btn-outline-primary">{{ __('patients.show_page.actions.open') }}</a>
                                                @endcan
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-muted py-4">{{ __('patients.show_page.empty.no_appointments') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if ($allAppointments->hasPages())
                            <div class="mt-3">{{ $allAppointments->links() }}</div>
                        @endif
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ __('patients.show_page.sections.completed_appointments') }}</h5>
                        <span class="badge bg-light text-dark">{{ $completedAppointments->total() }}</span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ __('patients.show_page.labels.appointment_no') }}</th>
                                        <th>{{ __('patients.show_page.labels.date') }}</th>
                                        <th>{{ __('patients.show_page.labels.doctor') }}</th>
                                        <th class="text-end">{{ __('common.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($completedAppointments as $appointment)
                                        <tr>
                                            <td>{{ $appointment->appointment_no ?? ('#' . $appointment->id) }}</td>
                                            <td>{{ $appointment->appointment_date?->format('M d, Y') ?? __('common.not_available') }}</td>
                                            <td>{{ $appointment->doctor?->display_name ?? $appointment->doctor?->full_name ?? __('common.not_available') }}</td>
                                            <td class="text-end">
                                                @can('appointments.view')
                                                    <a href="{{ route('admin.appointments.show', $appointment) }}" class="btn btn-sm btn-outline-primary">{{ __('patients.show_page.actions.open') }}</a>
                                                @endcan
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted py-4">{{ __('patients.show_page.empty.no_completed_appointments') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if ($completedAppointments->hasPages())
                            <div class="mt-3">{{ $completedAppointments->links() }}</div>
                        @endif
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ __('patients.show_page.sections.visit_history') }}</h5>
                        <span class="badge bg-light text-dark">{{ $visitHistory->total() }}</span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ __('patients.show_page.labels.visit_no') }}</th>
                                        <th>{{ __('patients.show_page.labels.date') }}</th>
                                        <th>{{ __('common.status') }}</th>
                                        <th class="text-end">{{ __('common.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($visitHistory as $visit)
                                        <tr>
                                            <td>{{ $visit->visit_no }}</td>
                                            <td>{{ $visit->visit_date?->format('M d, Y') ?? __('common.not_available') }}</td>
                                            <td>{{ $visit->status?->label() ?? ucfirst((string) $visit->status?->value) }}</td>
                                            <td class="text-end">
                                                @can('visits.view')
                                                    <a href="{{ route('admin.visits.show', $visit) }}" class="btn btn-sm btn-outline-primary">{{ __('patients.show_page.actions.open') }}</a>
                                                @endcan
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted py-4">{{ __('patients.show_page.empty.no_visits') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if ($visitHistory->hasPages())
                            <div class="mt-3">{{ $visitHistory->links() }}</div>
                        @endif
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ __('patients.show_page.sections.prescriptions') }}</h5>
                        <span class="badge bg-light text-dark">{{ $patientPrescriptions->total() }}</span>
                    </div>
                    <div class="card-body">
                        @can('prescriptions.view')
                            <div class="d-flex justify-content-end gap-2 mb-2">
                                @can('prescriptions.create')
                                    <a href="{{ route('admin.prescriptions.create', ['patient_id' => $patient->id]) }}" class="btn btn-outline-primary">
                                        {{ __('admin.prescriptions.create_prescription') }}
                                    </a>
                                @endcan
                                <a href="{{ route('admin.patients.prescriptions.printAll', $patient) }}" target="_blank" class="btn btn-outline-secondary">
                                    {{ __('patients.show_page.actions.print_all_prescriptions') }}
                                </a>
                            </div>
                        @endcan
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('patients.show_page.labels.date') }}</th>
                                        <th>{{ __('patients.show_page.labels.medicines_count') }}</th>
                                        <th class="text-end">{{ __('common.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($patientPrescriptions as $prescription)
                                        <tr>
                                            <td>#{{ $prescription->id }}</td>
                                            <td>{{ $prescription->issued_at?->format('M d, Y H:i') ?? __('common.not_available') }}</td>
                                            <td>{{ $prescription->items_count }}</td>
                                            <td class="text-end">
                                                @can('prescriptions.view')
                                                    <a href="{{ route('admin.patients.prescriptions.show', [$patient, $prescription]) }}" class="btn btn-sm btn-outline-primary">{{ __('patients.show_page.actions.open') }}</a>
                                                    <a href="{{ route('admin.patients.prescriptions.print', [$patient, $prescription]) }}" class="btn btn-sm btn-outline-secondary" target="_blank">{{ __('patients.show_page.actions.print_prescription') }}</a>
                                                @endcan
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted py-4">{{ __('patients.show_page.empty.no_prescriptions') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if ($patientPrescriptions->hasPages())
                            <div class="mt-3">{{ $patientPrescriptions->links() }}</div>
                        @endif
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ __('patients.show_page.sections.recent_timeline') }}</h5>
                        <a href="{{ route('admin.patients.show', ['patient' => $patient->id, 'tab' => 'timeline']) }}" class="btn btn-sm btn-outline-primary">{{ __('patients.show_page.actions.view_full_timeline') }}</a>
                    </div>
                    <div class="card-body">
                        @forelse ($timeline->take(5) as $item)
                            <div class="border-start border-3 ps-3 mb-3">
                                <div class="small text-muted">{{ ucfirst($item['type']) }} • {{ \Illuminate\Support\Carbon::parse($item['date'])->format('M d, Y H:i') }}</div>
                                <div class="fw-semibold">{{ $item['title'] }}</div>
                                <div>{{ $item['subtitle'] }}</div>
                                @if (filled($item['description']))
                                    <div class="text-muted small">{{ $item['description'] }}</div>
                                @endif
                            </div>
                        @empty
                            <p class="text-muted mb-0">{{ __('patients.show_page.empty.no_activity') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white"><h5 class="mb-0 text-danger">{{ __('patients.show_page.sections.alerts_allergies') }}</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="text-muted small">{{ __('patients.form.fields.allergies') }}</div>
                            <div>{{ $patient->medicalHistory?->allergies ?: __('patients.show_page.empty.no_allergy_info') }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="text-muted small">{{ __('patients.show_page.labels.conditions') }}</div>
                            <div>{{ $patient->medicalHistory?->chronic_diseases ?: __('patients.show_page.empty.no_conditions') }}</div>
                        </div>
                        <div>
                            <div class="text-muted small">{{ __('patients.form.fields.important_alerts') }}</div>
                            <div>{{ $patient->medicalHistory?->important_alerts ?: __('patients.show_page.empty.no_alerts') }}</div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h5 class="mb-0">{{ __('patients.show_page.sections.emergency_contacts') }}</h5></div>
                    <div class="card-body">
                        @forelse ($patient->emergencyContacts as $contact)
                            <div class="mb-3 pb-3 border-bottom last:border-bottom-0">
                                <div class="fw-semibold">{{ $contact->name }}</div>
                                <div>{{ $contact->relation ?: __('patients.show_page.empty.relation_not_set') }}</div>
                                <div class="text-muted small">{{ $contact->phone }}</div>
                                @if ($contact->notes)
                                    <div class="text-muted small">{{ $contact->notes }}</div>
                                @endif
                            </div>
                        @empty
                            <p class="text-muted mb-0">{{ __('patients.show_page.empty.no_contacts') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @elseif ($activeTab === 'history')
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ __('patients.show_page.sections.medical_history') }}</h5>
                <span class="text-muted small">{{ __('patients.show_page.labels.last_updated') }} {{ $patient->medicalHistory?->updated_at?->diffForHumans() ?? __('patients.show_page.empty.never') }}</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.patients.medical-history.store', $patient) }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('patients.form.fields.allergies') }}</label>
                            <textarea name="allergies" rows="4" class="form-control">{{ old('allergies', $patient->medicalHistory?->allergies) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('patients.form.fields.conditions') }}</label>
                            <textarea name="chronic_diseases" rows="4" class="form-control">{{ old('chronic_diseases', $patient->medicalHistory?->chronic_diseases) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('patients.form.fields.current_medications') }}</label>
                            <textarea name="current_medications" rows="4" class="form-control">{{ old('current_medications', $patient->medicalHistory?->current_medications) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('patients.form.fields.important_alerts') }}</label>
                            <textarea name="important_alerts" rows="4" class="form-control">{{ old('important_alerts', $patient->medicalHistory?->important_alerts) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('patients.form.fields.dental_history') }}</label>
                            <textarea name="dental_history" rows="4" class="form-control">{{ old('dental_history', $patient->medicalHistory?->dental_history) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('patients.form.fields.medical_notes') }}</label>
                            <textarea name="medical_notes" rows="4" class="form-control">{{ old('medical_notes', $patient->medicalHistory?->medical_notes) }}</textarea>
                        </div>
                    </div>
                    <div class="mt-4 d-flex justify-content-between align-items-center">
                        <div class="text-muted small">{{ __('patients.show_page.labels.updated_by') }} {{ $patient->medicalHistory?->updatedBy?->displayName ?? __('common.not_available') }}</div>
                        <button type="submit" class="btn btn-primary">{{ __('patients.show_page.actions.save_medical_history') }}</button>
                    </div>
                </form>
            </div>
        </div>
    @elseif ($activeTab === 'contacts')
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h5 class="mb-0">{{ __('patients.show_page.actions.add_emergency_contact') }}</h5></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.patients.emergency-contacts.store', $patient) }}">
                            @csrf
                            <div class="mb-3"><label class="form-label">{{ __('patients.form.fields.contact_name') }}</label><input type="text" name="name" class="form-control" required></div>
                            <div class="mb-3"><label class="form-label">{{ __('patients.form.fields.relation') }}</label><input type="text" name="relation" class="form-control"></div>
                            <div class="mb-3"><label class="form-label">{{ __('patients.form.fields.phone') }}</label><input type="text" name="phone" class="form-control" required></div>
                            <div class="mb-3"><label class="form-label">{{ __('patients.form.fields.notes') }}</label><textarea name="notes" rows="3" class="form-control"></textarea></div>
                            <button type="submit" class="btn btn-primary">{{ __('patients.show_page.actions.add_contact') }}</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h5 class="mb-0">{{ __('patients.show_page.sections.existing_contacts') }}</h5></div>
                    <div class="card-body">
                        @forelse ($patient->emergencyContacts as $contact)
                            <form method="POST" action="{{ route('admin.patients.emergency-contacts.update', [$patient, $contact->id]) }}" class="border rounded-3 p-3 mb-3">
                                @csrf
                                @method('PUT')
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label">{{ __('patients.form.fields.contact_name') }}</label><input type="text" name="name" class="form-control" value="{{ $contact->name }}" required></div>
                                    <div class="col-md-6"><label class="form-label">{{ __('patients.form.fields.relation') }}</label><input type="text" name="relation" class="form-control" value="{{ $contact->relation }}"></div>
                                    <div class="col-md-6"><label class="form-label">{{ __('patients.form.fields.phone') }}</label><input type="text" name="phone" class="form-control" value="{{ $contact->phone }}" required></div>
                                    <div class="col-md-6"><label class="form-label">{{ __('patients.form.fields.notes') }}</label><input type="text" name="notes" class="form-control" value="{{ $contact->notes }}"></div>
                                </div>
                                <div class="d-flex justify-content-between mt-3">
                                    <button type="submit" class="btn btn-outline-primary btn-sm">{{ __('common.save') }}</button>
                                    <button type="submit" form="delete-contact-{{ $contact->id }}" class="btn btn-outline-danger btn-sm">{{ __('common.delete') }}</button>
                                </div>
                            </form>
                            <form id="delete-contact-{{ $contact->id }}" method="POST" action="{{ route('admin.patients.emergency-contacts.destroy', [$patient, $contact->id]) }}" class="d-none">
                                @csrf
                                @method('DELETE')
                            </form>
                        @empty
                            <p class="text-muted mb-0">{{ __('patients.show_page.empty.no_contacts') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @elseif ($activeTab === 'files')
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h5 class="mb-0">{{ __('patients.form.sections.upload_medical_file') }}</h5></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.patients.medical-files.store', $patient) }}" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3"><label class="form-label">{{ __('patients.form.fields.file') }}</label><input type="file" name="file" class="form-control" required></div>
                            <div class="mb-3"><label class="form-label">{{ __('patients.form.fields.title') }}</label><input type="text" name="title" class="form-control" required></div>
                            <div class="mb-3">
                                <label class="form-label">{{ __('patients.form.fields.category') }}</label>
                                <select name="file_category" class="form-select">
                                    <option value="xray">{{ __('patients.show_page.file_categories.xray') }}</option>
                                    <option value="prescription">{{ __('patients.show_page.file_categories.prescription') }}</option>
                                    <option value="treatment_document">{{ __('patients.show_page.file_categories.treatment_document') }}</option>
                                    <option value="before_after">{{ __('patients.show_page.file_categories.before_after') }}</option>
                                    <option value="lab_result">{{ __('patients.show_page.file_categories.lab_result') }}</option>
                                    <option value="other" selected>{{ __('patients.show_page.file_categories.other') }}</option>
                                </select>
                            </div>
                            <div class="mb-3"><label class="form-label">{{ __('patients.form.fields.notes') }}</label><textarea name="notes" rows="3" class="form-control"></textarea></div>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="is_visible_to_patient" name="is_visible_to_patient" value="1" checked>
                                <label class="form-check-label" for="is_visible_to_patient">{{ __('patients.form.fields.visible_to_patient') }}</label>
                            </div>
                            <button type="submit" class="btn btn-primary">{{ __('patients.show_page.actions.upload_file') }}</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h5 class="mb-0">{{ __('patients.show_page.sections.uploaded_files') }}</h5></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>{{ __('patients.form.fields.title') }}</th>
                                        <th>{{ __('patients.form.fields.category') }}</th>
                                        <th>{{ __('patients.show_page.labels.uploaded') }}</th>
                                        <th>{{ __('patients.show_page.labels.visible') }}</th>
                                        <th class="text-end">{{ __('common.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($patient->medicalFiles as $file)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $file->title }}</div>
                                                <div class="text-muted small">{{ $file->file_name }}</div>
                                            </td>
                                            <td>{{ str($file->file_category?->value ?? $file->file_category)->replace('_', ' ')->title() }}</td>
                                            <td>{{ $file->uploaded_at?->format('M d, Y H:i') ?: __('common.none') }}</td>
                                            <td>{{ $file->is_visible_to_patient ? __('patients.show_page.labels.yes') : __('patients.show_page.labels.no') }}</td>
                                            <td class="text-end">
                                                @if ($file->file_path)
                                                    <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($file->file_path) }}" class="btn btn-sm btn-outline-primary" target="_blank">{{ __('patients.show_page.actions.open') }}</a>
                                                @endif
                                                <form method="POST" action="{{ route('admin.patients.medical-files.destroy', [$patient, $file->id]) }}" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('common.delete') }}</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-muted py-4">{{ __('patients.show_page.empty.no_files') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ __('patients.show_page.sections.patient_timeline') }}</h5>
                <span class="text-muted small">{{ __('patients.show_page.timeline_subtitle') }}</span>
            </div>
            <div class="card-body">
                @forelse ($timeline as $item)
                    <div class="d-flex gap-3 pb-4 mb-4 border-bottom">
                        <div>
                            <span class="badge bg-light text-dark border">{{ ucfirst($item['type']) }}</span>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between flex-wrap gap-2">
                                <div>
                                    <div class="fw-semibold">{{ $item['title'] }}</div>
                                    <div class="text-muted small">{{ $item['subtitle'] }}</div>
                                </div>
                                <div class="text-muted small">{{ \Illuminate\Support\Carbon::parse($item['date'])->format('M d, Y H:i') }}</div>
                            </div>
                            @if (filled($item['description']))
                                <div class="mt-2">{{ $item['description'] }}</div>
                            @endif
                            @if (!empty($item['route']))
                                <a href="{{ $item['route'] }}" class="btn btn-sm btn-outline-primary mt-2">{{ __('patients.show_page.actions.open') }}</a>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">{{ __('patients.show_page.empty.no_timeline') }}</p>
                @endforelse
            </div>
        </div>
    @endif
</div>
@endsection

