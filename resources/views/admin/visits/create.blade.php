@extends('admin.layouts.app')

@section('title', __('visits.create'))

@section('content')
@php
    $storeUrl = \Illuminate\Support\Facades\Route::has('admin.visits.store')
        ? route('admin.visits.store')
        : '#';
@endphp

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">{{ __('visits.create') }}</h1>
            <p class="text-muted mb-0">{{ __('visits.create_subtitle') }}</p>
        </div>
        <a href="{{ route('admin.visits.index') }}" class="btn btn-outline-secondary">{{ __('common.view') }}</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>{{ __('admin.validation_errors') }}</strong>
            <ul class="mb-0 mt-2 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (!\Illuminate\Support\Facades\Route::has('admin.visits.store'))
        <div class="alert alert-warning">
            {{ __('visits.not_configured_yet', ['route' => 'admin.visits.store']) }}
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ $storeUrl }}" method="POST">
                @csrf

                @include('admin.visits._form')

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary" @disabled(!\Illuminate\Support\Facades\Route::has('admin.visits.store'))>
                        {{ __('visits.create') }}
                    </button>
                    <a href="{{ route('admin.visits.index') }}" class="btn btn-outline-secondary">{{ __('common.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

