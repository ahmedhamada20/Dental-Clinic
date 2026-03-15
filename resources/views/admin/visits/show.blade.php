@extends('admin.layouts.app')

@section('content')
@php
    $selectedTab = request('tab', 'overview');
    $visitNotes  = $visit->notes ?? collect();
    $queueRoute  = \Illuminate\Support\Facades\Route::has('admin.visits.queue')
        ? route('admin.visits.queue')
        : route('admin.visits.index');
@endphp

<div class="container-fluid">
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">{{ __('visits.details') }}</h2>
                <div class="text-muted mt-1">
                    {{ __('visits.view') }} <strong>{{ $visit->visit_no }}</strong> &mdash; {{ $visit->visit_date->format('d M Y') }}
                </div>
            </div>
            <div class="col-auto d-flex gap-2">
                <a href="{{ $queueRoute }}" class="btn btn-outline-primary">{{ __('visits.back_to_queue') }}</a>
            </div>
        </div>
    </div>
</div>

<div class="page-wrapper">
    <div class="container-fluid">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>{{ __('admin.validation_errors') }}</strong>
                <ul class="mb-0 mt-2 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            {{-- ── Left Column ──────────────────────────────────────── --}}
            <div class="col-lg-8">

                {{-- Visit Info --}}
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">{{ __('visits.info') }}</h3>
                        <span class="badge badge-lg @switch($visit->status->value)
                            @case('scheduled')   bg-info    @break
                            @case('in_progress') bg-warning @break
                            @case('completed')   bg-success @break
                            @case('cancelled')   bg-danger  @break
                            @case('no_show')     bg-danger  @break
                            @default             bg-secondary
                        @endswitch">
                            {{ $visit->status->label() }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('visits.visit_no') }}</label>
                                <input type="text" class="form-control" value="{{ $visit->visit_no }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('visits.visit_date') }}</label>
                                <input type="text" class="form-control" value="{{ $visit->visit_date->format('Y-m-d') }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('visits.start_time') }}</label>
                                <input type="text" class="form-control" value="{{ $visit->start_at?->format('H:i:s') ?? __('visits.not_started') }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('visits.end_time') }}</label>
                                <input type="text" class="form-control" value="{{ $visit->end_at?->format('H:i:s') ?? __('visits.not_completed') }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Patient & Care Team --}}
                <div class="card mb-3">
                    <div class="card-header"><h3 class="card-title mb-0">{{ __('visits.care_team') }}</h3></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('visits.patient_name') }}</label>
                                <input type="text" class="form-control" value="{{ $visit->patient?->full_name ?? $visit->patient?->displayName ?? 'N/A' }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('visits.patient_id') }}</label>
                                <input type="text" class="form-control" value="{{ $visit->patient?->patient_code ?? 'N/A' }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('visits.doctor') }}</label>
                                <input type="text" class="form-control" value="{{ $visit->doctor?->display_name ?? $visit->doctor?->full_name ?? __('visits.unassigned') }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('visits.checked_in_by') }}</label>
                                <input type="text" class="form-control" value="{{ $visit->checkedInBy?->display_name ?? $visit->checkedInBy?->full_name ?? __('common.not_available') }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('visits.email') }}</label>
                                <input type="text" class="form-control" value="{{ $visit->patient?->email ?? __('common.not_available') }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('visits.phone') }}</label>
                                <input type="text" class="form-control" value="{{ $visit->patient?->phone ?? __('common.not_available') }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Clinical Summary (visit-level fields) --}}
                <div class="card mb-3">
                    <div class="card-header"><h3 class="card-title mb-0">{{ __('visits.clinical_summary') }}</h3></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('visits.complaints') }}</label>
                            <textarea class="form-control" rows="2" readonly>{{ $visit->chief_complaint ?? __('common.not_available') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('visits.diagnosis') }}</label>
                            <textarea class="form-control" rows="2" readonly>{{ $visit->diagnosis ?? __('common.not_available') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('visits.clinical_notes') }}</label>
                            <textarea class="form-control" rows="3" readonly>{{ $visit->clinical_notes ?? __('common.not_available') }}</textarea>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">{{ __('visits.internal_notes') }}</label>
                            <textarea class="form-control" rows="3" readonly>{{ $visit->internal_notes ?? __('common.not_available') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Related Appointment --}}
                @if ($visit->appointment)
                    <div class="card mb-3">
                        <div class="card-header"><h3 class="card-title mb-0">{{ __('visits.related_appointment') }}</h3></div>
                        <div class="card-body row g-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('visits.appointment_no') }}</label>
                                <input type="text" class="form-control" value="{{ $visit->appointment->appointment_no }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('visits.appointment_date') }}</label>
                                <input type="text" class="form-control" value="{{ $visit->appointment->appointment_date->format('Y-m-d H:i') }}" readonly>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Tabs: Overview | Visit Notes --}}
                <div class="card">
                    <div class="card-header border-0 pb-0">
                        <ul class="nav nav-tabs card-header-tabs" data-bs-toggle="tabs">
                            <li class="nav-item">
                                <a href="#tabs-overview" class="nav-link {{ $selectedTab === 'overview' ? 'active' : '' }}" data-bs-toggle="tab">{{ __('visits.overview') }}</a>
                            </li>
                            <li class="nav-item">
                                <a href="#tabs-notes" class="nav-link {{ $selectedTab === 'notes' ? 'active' : '' }}" data-bs-toggle="tab">{{ __('visits.notes') }}</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">

                            {{-- Overview Tab --}}
                            <div class="tab-pane {{ $selectedTab === 'overview' ? 'active show' : '' }}" id="tabs-overview">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="border rounded p-3 h-100">
                                            <div class="text-muted text-uppercase small">{{ __('visits.notes') }}</div>
                                            <div class="display-6">{{ $visitNotes->count() }}</div>
                                            <div class="text-muted">{{ __('visits.clinical_records') }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="border rounded p-3 h-100">
                                            <div class="text-muted text-uppercase small">{{ __('visits.treatment_plans') }}</div>
                                            <div class="display-6">{{ $visit->treatmentPlans->count() }}</div>
                                            <div class="text-muted">{{ __('visits.active_plans') }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="border rounded p-3 h-100">
                                            <div class="text-muted text-uppercase small">{{ __('visits.prescriptions') }}</div>
                                            <div class="display-6">{{ $visit->prescriptions->count() }}</div>
                                            <div class="text-muted">{{ __('visits.issued_this_visit') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Visit Notes Tab --}}
                            <div class="tab-pane {{ $selectedTab === 'notes' ? 'active show' : '' }}" id="tabs-notes">
                                <div class="row g-4">
                                    {{-- Add Note Form --}}
                                    <div class="col-lg-5">
                                        <div class="border rounded p-3">
                                            <h4 class="mb-3">{{ __('visits.add_note') }}</h4>
                                            <form method="POST" action="{{ route('admin.visits.notes.store', $visit) }}">
                                                @csrf
                                                <div class="mb-3">
                                                    <label class="form-label">{{ __('visits.diagnosis') }} <span class="text-muted small">(optional)</span></label>
                                                    <textarea name="diagnosis" rows="2" class="form-control" placeholder="Clinical diagnosis">{{ old('diagnosis') }}</textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">{{ __('visits.note') }} <span class="text-danger">*</span></label>
                                                    <textarea name="note" rows="4" class="form-control" required placeholder="Clinical observations, findings, remarks…">{{ old('note') }}</textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">{{ __('visits.treatment_plan_note') }} <span class="text-muted small">(optional)</span></label>
                                                    <textarea name="treatment_plan" rows="2" class="form-control" placeholder="Proposed treatment or next steps">{{ old('treatment_plan') }}</textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">{{ __('visits.follow_up_date') }} <span class="text-muted small">(optional)</span></label>
                                                    <input type="date" name="follow_up_date" class="form-control" value="{{ old('follow_up_date') }}" min="{{ now()->toDateString() }}">
                                                </div>
                                                <button class="btn btn-primary w-100" type="submit">{{ __('visits.save_note') }}</button>
                                            </form>
                                        </div>
                                    </div>

                                    {{-- Notes List --}}
                                    <div class="col-lg-7">
                                        @forelse ($visitNotes as $note)
                                            <div class="card mb-3">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                                        <div>
                                                            <div class="fw-bold">
                                                                {{ $note->doctor?->display_name ?? $note->doctor?->full_name ?? $note->createdBy?->display_name ?? $note->createdBy?->full_name ?? 'Unknown' }}
                                                            </div>
                                                            <div class="text-muted small">
                                                                {{ $note->created_at?->format('Y-m-d H:i') }}
                                                                @if ($note->follow_up_date)
                                                                    &middot; Follow-up: <strong>{{ $note->follow_up_date->format('Y-m-d') }}</strong>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <form method="POST" action="{{ route('admin.visits.notes.destroy', [$visit, $note]) }}" onsubmit="return confirm('{{ __('visits.delete_note_confirm') }}');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="btn btn-sm btn-outline-danger" type="submit">{{ __('common.delete') }}</button>
                                                        </form>
                                                    </div>

                                                    @if ($note->diagnosis)
                                                        <div class="mb-2">
                                                            <span class="text-muted small text-uppercase">{{ __('visits.diagnosis') }}</span>
                                                            <div>{{ $note->diagnosis }}</div>
                                                        </div>
                                                    @endif

                                                    <div class="mb-2">
                                                        <span class="text-muted small text-uppercase">{{ __('visits.note') }}</span>
                                                        <div>{{ $note->note }}</div>
                                                    </div>

                                                    @if ($note->treatment_plan)
                                                        <div class="mb-2">
                                                            <span class="text-muted small text-uppercase">{{ __('visits.treatment_plan_note') }}</span>
                                                            <div>{{ $note->treatment_plan }}</div>
                                                        </div>
                                                    @endif

                                                    @if ($note->attachments && count($note->attachments))
                                                        <div class="mb-2">
                                                            <span class="text-muted small text-uppercase">{{ __('visits.attachments') }}</span>
                                                            <ul class="mb-0 ps-3">
                                                                @foreach ($note->attachments as $attachment)
                                                                    <li class="small">{{ $attachment }}</li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endif

                                                    {{-- Inline edit --}}
                                                    <details class="mt-3">
                                                        <summary class="small text-primary">{{ __('visits.edit_note') }}</summary>
                                                        <form method="POST" action="{{ route('admin.visits.notes.update', [$visit, $note]) }}" class="mt-3">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="mb-2">
                                                                <label class="form-label small">{{ __('visits.diagnosis') }}</label>
                                                                <textarea name="diagnosis" rows="2" class="form-control form-control-sm">{{ $note->diagnosis }}</textarea>
                                                            </div>
                                                            <div class="mb-2">
                                                                <label class="form-label small">{{ __('visits.note') }} <span class="text-danger">*</span></label>
                                                                <textarea name="note" rows="3" class="form-control form-control-sm" required>{{ $note->note }}</textarea>
                                                            </div>
                                                            <div class="mb-2">
                                                                <label class="form-label small">{{ __('visits.treatment_plan_note') }}</label>
                                                                <textarea name="treatment_plan" rows="2" class="form-control form-control-sm">{{ $note->treatment_plan }}</textarea>
                                                            </div>
                                                            <div class="mb-2">
                                                                <label class="form-label small">{{ __('visits.follow_up_date') }}</label>
                                                                <input type="date" name="follow_up_date" class="form-control form-control-sm" value="{{ $note->follow_up_date?->format('Y-m-d') }}" min="{{ now()->toDateString() }}">
                                                            </div>
                                                            <div class="text-end">
                                                                <button class="btn btn-sm btn-primary" type="submit">{{ __('visits.update_note') }}</button>
                                                            </div>
                                                        </form>
                                                    </details>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-muted">{{ __('visits.no_notes') }}</div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Right Column ─────────────────────────────────────── --}}
            <div class="col-lg-4">

                {{-- Workflow Actions --}}
                <div class="card mb-3">
                    <div class="card-header"><h3 class="card-title mb-0">{{ __('visits.workflow_actions') }}</h3></div>
                    <div class="card-body">
                        @if ($visit->status->value === 'scheduled')
                            <form method="POST" action="{{ route('admin.visits.start', $visit) }}" class="mb-2">
                                @csrf
                                <button type="submit" class="btn btn-primary w-100">{{ __('visits.start_visit') }}</button>
                            </form>
                        @elseif ($visit->status->value === 'in_progress')
                            <button type="button" class="btn btn-success w-100 mb-2" data-bs-toggle="modal" data-bs-target="#completeVisitModal">
                                {{ __('visits.complete_visit') }}
                            </button>
                        @endif

                        @if (! in_array($visit->status->value, ['completed', 'cancelled']))
                            <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#cancelVisitModal">
                                {{ __('visits.cancel_visit') }}
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Queue Ticket --}}
                @if ($visit->ticket)
                    <div class="card mb-3">
                        <div class="card-header"><h3 class="card-title mb-0">{{ __('visits.queue_ticket') }}</h3></div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <div class="display-6 fw-bold text-primary">{{ $visit->ticket->ticket_number }}</div>
                                <small class="text-muted">{{ __('visits.ticket_number') }}</small>
                            </div>
                            <div class="mb-2"><div class="text-muted small">{{ __('common.status') }}</div><strong>{{ $visit->ticket->status->label() }}</strong></div>
                            <div class="mb-2"><div class="text-muted small">{{ __('visits.created_at_label') }}</div><strong>{{ $visit->ticket->created_at->format('Y-m-d H:i:s') }}</strong></div>
                            @if ($visit->ticket->called_at)
                                <div class="mb-2"><div class="text-muted small">{{ __('visits.called_at') }}</div><strong>{{ $visit->ticket->called_at->format('Y-m-d H:i:s') }}</strong></div>
                            @endif
                            @if ($visit->ticket->finished_at)
                                <div><div class="text-muted small">{{ __('visits.finished_at') }}</div><strong>{{ $visit->ticket->finished_at->format('Y-m-d H:i:s') }}</strong></div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Visit Summary --}}
                <div class="card mb-3">
                    <div class="card-header"><h3 class="card-title mb-0">{{ __('visits.visit_summary') }}</h3></div>
                    <div class="card-body">
                        <div class="mb-2"><div class="text-muted small">{{ __('visits.notes_this_visit') }}</div><strong>{{ $visitNotes->count() }}</strong></div>
                        <div class="mb-2"><div class="text-muted small">{{ __('visits.treatment_plans') }}</div><strong>{{ $visit->treatmentPlans->count() }}</strong></div>
                        <div class="mb-2"><div class="text-muted small">{{ __('visits.prescriptions') }}</div><strong>{{ $visit->prescriptions->count() }}</strong></div>
                        <div class="mb-2"><div class="text-muted small">{{ __('visits.invoices') }}</div><strong>{{ $visit->invoice?->invoice_no ?? __('visits.not_issued') }}</strong></div>
                        <div class="mb-0"><div class="text-muted small">{{ __('visits.medical_files') }}</div><strong>{{ $visit->medicalFiles->count() }}</strong></div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- Complete Visit Modal --}}
<div class="modal modal-blur fade" id="completeVisitModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <form method="POST" action="{{ route('admin.visits.complete', $visit) }}">
                @csrf
                <div class="modal-header"><h5 class="modal-title">{{ __('visits.complete_visit') }}</h5></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('visits.complaints') }}</label>
                        <textarea class="form-control @error('chief_complaint') is-invalid @enderror" name="chief_complaint" rows="2">{{ old('chief_complaint', $visit->chief_complaint) }}</textarea>
                        @error('chief_complaint') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('visits.diagnosis') }}</label>
                        <textarea class="form-control @error('diagnosis') is-invalid @enderror" name="diagnosis" rows="2">{{ old('diagnosis', $visit->diagnosis) }}</textarea>
                        @error('diagnosis') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('visits.clinical_notes') }}</label>
                        <textarea class="form-control @error('clinical_notes') is-invalid @enderror" name="clinical_notes" rows="2">{{ old('clinical_notes', $visit->clinical_notes) }}</textarea>
                        @error('clinical_notes') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-0">
                        <label class="form-label">{{ __('visits.internal_notes') }}</label>
                        <textarea class="form-control @error('internal_notes') is-invalid @enderror" name="internal_notes" rows="2">{{ old('internal_notes', $visit->internal_notes) }}</textarea>
                        @error('internal_notes') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('visits.complete_visit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Cancel Visit Modal --}}
<div class="modal modal-blur fade" id="cancelVisitModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <form method="POST" action="{{ route('admin.visits.cancel', $visit) }}">
                @csrf
                <div class="modal-header"><h5 class="modal-title">{{ __('visits.cancel_visit') }}</h5></div>
                <div class="modal-body">
                    <div class="alert alert-warning" role="alert">
                        {{ __('visits.cancel_confirm') }}
                    </div>
                    <div class="mb-0">
                        <label class="form-label">{{ __('visits.cancellation_reason') }} <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('reason') is-invalid @enderror" name="reason" rows="3" required></textarea>
                        @error('reason') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" class="btn btn-danger">{{ __('visits.cancel_visit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
