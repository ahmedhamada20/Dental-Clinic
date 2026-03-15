@extends('admin.layouts.app')

@section('title', __('admin.notifications.compose_announcement'))

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.notifications.index') }}" class="btn btn-sm btn-outline-secondary me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 class="h4 mb-0"><i class="bi bi-megaphone-fill text-primary me-2"></i>{{ __('admin.notifications.compose_announcement') }}</h1>
            <p class="text-muted mb-0 small">{{ __('admin.notifications.compose_subtitle') }}</p>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">
        {{-- Compose form --}}
        <div class="col-lg-8">
            <form action="{{ route('admin.notifications.store') }}" method="POST">
                @csrf

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white fw-semibold">
                        <i class="bi bi-pencil-square me-2 text-primary"></i>{{ __('admin.notifications.message_content') }}
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-medium">{{ __('admin.notifications.title_label') }} <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                   placeholder="{{ __('admin.notifications.title_placeholder') }}" value="{{ old('title') }}" maxlength="255">
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium">{{ __('admin.notifications.message_body') }} <span class="text-danger">*</span></label>
                            <textarea name="body" rows="5" maxlength="4000"
                                      class="form-control @error('body') is-invalid @enderror"
                                      placeholder="{{ __('admin.notifications.message_placeholder') }}">{{ old('body') }}</textarea>
                            @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text text-end"><span id="bodyCount">0</span> / 4000 {{ __('admin.notifications.chars') }}</div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white fw-semibold">
                        <i class="bi bi-people-fill me-2 text-success"></i>{{ __('admin.notifications.audience') }}
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-medium">{{ __('admin.notifications.target_audience') }} <span class="text-danger">*</span></label>
                            <select name="audience" id="audienceSelect"
                                    class="form-select @error('audience') is-invalid @enderror">
                                <option value="all_patients"    @selected(old('audience') === 'all_patients')>{{ __('admin.notifications.audience_options.all_patients') }}</option>
                                <option value="active_patients" @selected(old('audience') === 'active_patients')>{{ __('admin.notifications.audience_options.active_patients') }}</option>
                                <option value="patient_ids"     @selected(old('audience') === 'patient_ids')>{{ __('admin.notifications.audience_options.specific_patients') }}</option>
                            </select>
                            @error('audience')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        {{-- Specific patient picker (shown only when patient_ids selected) --}}
                        <div id="patientPickerWrap" style="display:none;">
                            <label class="form-label fw-medium">{{ __('admin.notifications.select_patients') }}</label>
                            <select name="patient_ids[]" id="patientPicker" class="form-select" multiple size="6">
                                @foreach($patients as $p)
                                    <option value="{{ $p->id }}"
                                        @if(is_array(old('patient_ids')) && in_array($p->id, old('patient_ids'))) selected @endif>
                                        {{ $p->full_name }} — {{ $p->email ?? $p->phone ?? __('admin.notifications.no_contact') }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">{{ __('admin.notifications.select_patients_help') }}</div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white fw-semibold">
                        <i class="bi bi-broadcast me-2 text-warning"></i>{{ __('admin.notifications.delivery_channels') }}
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            @foreach($channels as $ch)
                                @php
                                    $icon = match($ch) {
                                        'email'    => 'bi-envelope-fill text-info',
                                        'sms'      => 'bi-chat-dots-fill text-success',
                                        'push'     => 'bi-phone-fill text-warning',
                                        'database',
                                        'in_app'   => 'bi-bell-fill text-primary',
                                        default    => 'bi-circle text-secondary',
                                    };
                                    $desc = match($ch) {
                                        'email'    => __('admin.notifications.channel_descriptions.email'),
                                        'sms'      => __('admin.notifications.channel_descriptions.sms'),
                                        'push'     => __('admin.notifications.channel_descriptions.push'),
                                        'database' => __('admin.notifications.channel_descriptions.database'),
                                        'in_app'   => __('admin.notifications.channel_descriptions.in_app'),
                                        default    => '',
                                    };
                                    $checked = is_array(old('channels'))
                                        ? in_array($ch, old('channels'))
                                        : in_array($ch, ['database', 'email']);
                                @endphp
                                <div class="col-6 col-md-4">
                                    <label class="d-flex align-items-center border rounded p-2 cursor-pointer
                                        {{ $checked ? 'border-primary bg-primary bg-opacity-10' : '' }}"
                                        style="cursor:pointer;">
                                        <input type="checkbox" name="channels[]" value="{{ $ch }}"
                                               class="form-check-input me-2" {{ $checked ? 'checked' : '' }}>
                                        <div>
                                            <i class="bi {{ $icon }} me-1"></i>
                                            <span class="fw-medium">{{ __('admin.notifications.channels.' . $ch) }}</span><br>
                                            <small class="text-muted">{{ $desc }}</small>
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @error('channels')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send-fill me-1"></i>{{ __('admin.notifications.send_announcement') }}
                    </button>
                    <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-secondary">{{ __('admin.cancel') }}</a>
                </div>
            </form>
        </div>

        {{-- Sidebar: other workflow triggers --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold small">
                    <i class="bi bi-lightning-charge-fill text-warning me-1"></i>{{ __('admin.notifications.other_workflow_triggers') }}
                </div>
                <div class="card-body d-grid gap-2">
                    <form action="{{ route('admin.notifications.send-appointment-reminders') }}" method="POST">
                        @csrf
                        <input type="hidden" name="channels[]" value="database">
                        <input type="hidden" name="channels[]" value="email">
                        <button class="btn btn-outline-primary btn-sm w-100"
                            onclick="return confirm('{{ __('admin.notifications.confirm_send_appointment_reminders_tomorrow') }}')">
                            <i class="bi bi-calendar-check me-1"></i>
                            {{ __('admin.notifications.appointment_reminders_tomorrow') }}
                        </button>
                    </form>
                    <form action="{{ route('admin.notifications.send-billing-reminders') }}" method="POST">
                        @csrf
                        <input type="hidden" name="channels[]" value="database">
                        <input type="hidden" name="channels[]" value="email">
                        <button class="btn btn-outline-warning btn-sm w-100"
                            onclick="return confirm('{{ __('admin.notifications.confirm_send_billing_reminders_overdue') }}')">
                            <i class="bi bi-receipt-cutoff me-1"></i>
                            {{ __('admin.notifications.billing_due_reminders') }}
                        </button>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold small">
                    <i class="bi bi-info-circle me-1 text-info"></i>{{ __('admin.notifications.channel_guide') }}
                </div>
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item">
                        <i class="bi bi-bell-fill text-primary me-1"></i>
                        <strong>{{ __('admin.notifications.channel_guide_labels.database_in_app') }}</strong> — {{ __('admin.notifications.channel_guide_help.database_in_app') }}
                    </li>
                    <li class="list-group-item">
                        <i class="bi bi-envelope-fill text-info me-1"></i>
                        <strong>{{ __('admin.notifications.channels.email') }}</strong> — {{ __('admin.notifications.channel_guide_help.email') }}
                    </li>
                    <li class="list-group-item">
                        <i class="bi bi-chat-dots-fill text-success me-1"></i>
                        <strong>{{ __('admin.notifications.channels.sms') }}</strong> — {{ __('admin.notifications.channel_guide_help.sms') }}
                    </li>
                    <li class="list-group-item">
                        <i class="bi bi-phone-fill text-warning me-1"></i>
                        <strong>{{ __('admin.notifications.channels.push') }}</strong> — {{ __('admin.notifications.channel_guide_help.push') }}
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Body char counter
    const bodyTA = document.querySelector('[name="body"]');
    const counter = document.getElementById('bodyCount');
    if (bodyTA && counter) {
        counter.textContent = bodyTA.value.length;
        bodyTA.addEventListener('input', () => counter.textContent = bodyTA.value.length);
    }

    // Show/hide specific patient picker
    const audienceSelect = document.getElementById('audienceSelect');
    const patientWrap    = document.getElementById('patientPickerWrap');
    function togglePicker() {
        patientWrap.style.display = audienceSelect.value === 'patient_ids' ? '' : 'none';
    }
    audienceSelect?.addEventListener('change', togglePicker);
    togglePicker();
</script>
@endpush
@endsection

