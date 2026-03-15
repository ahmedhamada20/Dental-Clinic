@extends('admin.layouts.app')

@section('title', __('patients.create'))

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">{{ __('patients.create_page.heading') }}</h1>
            <p class="text-muted mb-0">{{ __('patients.create_page.subtitle') }}</p>
        </div>
        <a href="{{ route('admin.patients.index') }}" class="btn btn-outline-secondary">{{ __('patients.actions.back_to_patients') }}</a>
    </div>

    <form method="POST" action="{{ route('admin.patients.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.patients._form', ['submitLabel' => __('patients.actions.create')])
    </form>
</div>
@endsection

