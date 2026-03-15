@extends('admin.layouts.app')

@section('title', __('admin.prescriptions.edit_prescription'))

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('admin.prescriptions.edit_prescription') }}</h1>
            <p class="text-muted mb-0">{{ __('admin.prescriptions.edit_subtitle', ['id' => $prescription->id]) }}</p>
        </div>
        <a href="{{ route('admin.prescriptions.show', $prescription) }}" class="btn btn-outline-secondary">{{ __('admin.back') }}</a>
    </div>

    <form method="POST" action="{{ route('admin.prescriptions.update', $prescription) }}">
        @csrf
        @method('PUT')
        @include('admin.prescriptions._form')
    </form>
</div>
@endsection

