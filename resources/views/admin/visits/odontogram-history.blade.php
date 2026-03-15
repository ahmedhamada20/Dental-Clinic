@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">{{ __('visits.odontogram_history') }}</h2>
                <div class="text-muted mt-1">{{ __('visits.odontogram_history_subtitle', ['visit_no' => $visit->visit_no]) }}</div>
            </div>
            <div class="col-auto">
                <a href="{{ route('admin.visits.show', ['visit' => $visit->id, 'tab' => 'odontogram']) }}" class="btn btn-outline-primary">{{ __('visits.back_to_visit_details') }}</a>
            </div>
        </div>
    </div>
</div>

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">{{ __('visits.filter_by_tooth_number') }}</label>
                        <input type="text" name="tooth_number" value="{{ $selectedTooth }}" class="form-control" placeholder="{{ __('visits.tooth_example') }}">
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary" type="submit">{{ __('visits.apply_filter') }}</button>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('admin.visits.odontogram-history.index', $visit) }}" class="btn btn-outline-secondary">{{ __('common.reset') }}</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0">{{ __('visits.history_timeline') }}</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter">
                    <thead>
                        <tr>
                            <th>{{ __('common.date') }}</th>
                            <th>{{ __('visits.tooth') }}</th>
                            <th>{{ __('visits.transition') }}</th>
                            <th>{{ __('visits.surface') }}</th>
                            <th>{{ __('visits.view') }}</th>
                            <th>{{ __('visits.changed_by') }}</th>
                            <th>{{ __('visits.notes') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($history as $entry)
                            <tr>
                                <td>{{ $entry->created_at?->format('Y-m-d H:i') }}</td>
                                <td class="fw-bold">{{ $entry->tooth_number }}</td>
                                <td>
                                    {{ $entry->old_status ? str($entry->old_status)->replace('_', ' ')->title() : __('visits.new_record') }}
                                    <span class="text-muted">→</span>
                                    {{ str($entry->new_status)->replace('_', ' ')->title() }}
                                </td>
                                <td>{{ $entry->surface ?: '—' }}</td>
                                <td>{{ $entry->visit?->visit_no ?? '—' }}</td>
                                <td>{{ $entry->changedBy->displayName ?? $entry->changedBy->name ?? __('visits.system') }}</td>
                                <td>{{ $entry->notes ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-muted">{{ __('visits.no_odontogram_history') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($history->hasPages())
                <div class="card-footer">
                    {{ $history->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

