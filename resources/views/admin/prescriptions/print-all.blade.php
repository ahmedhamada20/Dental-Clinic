@extends('admin.layouts.print')
@section('title', __('patients.show_page.actions.print_all_prescriptions'))
@section('content')
    <h2>{{ __('patients.show_page.actions.print_all_prescriptions') }} - {{ $patient->display_name }}</h2>
    @forelse($prescriptions as $prescription)
        <div class="prescription-block" style="page-break-after: always;">
            <h4>{{ __('prescriptions.prescription') }} #{{ $prescription->id }}</h4>
            <div><b>{{ __('prescriptions.date') }}:</b> {{ $prescription->issued_at?->format('M d, Y H:i') }}</div>
            <div><b>{{ __('prescriptions.doctor') }}:</b> {{ $prescription->doctor?->display_name ?? __('common.not_available') }}</div>
            <div><b>{{ __('prescriptions.visit') }}:</b> {{ $prescription->visit?->visit_no ?? __('common.not_available') }}</div>
            <div><b>{{ __('prescriptions.notes') }}:</b> {{ $prescription->notes }}</div>
            <div><b>{{ __('prescriptions.medicines') }}:</b></div>
            <ul>
                @foreach($prescription->items as $item)
                    <li>{{ $item->medicine_name }} - {{ $item->dosage }} - {{ $item->instructions }}</li>
                @endforeach
            </ul>
        </div>
    @empty
        <div class="alert alert-info">{{ __('patients.show_page.empty.no_prescriptions') }}</div>
    @endforelse
@endsection

