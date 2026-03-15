@extends('admin.layouts.app')

@section('title', __('appointments.pages.create.title'))

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-1">{{ __('appointments.pages.create.title') }}</h1>
            <p class="text-muted mb-0">{{ __('appointments.pages.create.subtitle') }}</p>
        </div>
        <a href="{{ route('admin.appointments.index') }}" class="btn btn-outline-secondary">{{ __('appointments.pages.create.back') }}</a>
    </div>

    <form method="POST" action="{{ route('admin.appointments.store') }}">
        @csrf
        @include('admin.appointments._form', ['appointment' => null])
    </form>
</div>
@endsection

