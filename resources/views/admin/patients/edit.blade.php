@extends('admin.layouts.app')

@section('title', __('patients.edit'))

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">{{ __('patients.edit_page.heading') }}</h1>
            <p class="text-muted mb-0">{{ __('patients.edit_page.subtitle') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.patients.show', $patient) }}" class="btn btn-outline-primary">{{ __('patients.actions.view_record') }}</a>
            <a href="{{ route('admin.patients.index') }}" class="btn btn-outline-secondary">{{ __('patients.actions.back_to_patients') }}</a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.patients.update', $patient) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.patients._form', ['submitLabel' => __('patients.actions.save_changes')])
    </form>
</div>
@endsection

