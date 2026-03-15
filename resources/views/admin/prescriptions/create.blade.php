@extends('admin.layouts.app')

@section('title', __('admin.prescriptions.create_prescription'))

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('admin.prescriptions.create_prescription') }}</h1>
            <p class="text-muted mb-0">{{ __('admin.prescriptions.create_subtitle') }}</p>
        </div>
        <a href="{{ route('admin.prescriptions.index') }}" class="btn btn-outline-secondary">{{ __('admin.back') }}</a>
    </div>

    <form method="POST" action="{{ route('admin.prescriptions.store') }}">
        @csrf
        @include('admin.prescriptions._form')
    </form>
</div>
@endsection

