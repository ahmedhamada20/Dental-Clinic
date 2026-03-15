@extends('admin.layouts.app')

@section('title', __('visits.edit'))

@section('content')
@php
    $canUpdate = \Illuminate\Support\Facades\Route::has('admin.visits.update');
    $canDelete = \Illuminate\Support\Facades\Route::has('admin.visits.destroy');
    $updateUrl = $canUpdate ? route('admin.visits.update', $visit) : '#';
    $showUrl = route('admin.visits.show', $visit);
@endphp

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">{{ __('visits.edit') }}</h1>
            <p class="text-muted mb-0">{{ __('visits.edit_subtitle', ['visit_no' => $visit->visit_no]) }}</p>
        </div>
        <a href="{{ $showUrl }}" class="btn btn-outline-primary">{{ __('common.view') }}</a>
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

    @if (!$canUpdate)
        <div class="alert alert-warning">
            {{ __('visits.not_configured_yet', ['route' => 'admin.visits.update']) }}
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ $updateUrl }}" method="POST">
                @csrf
                @method('PUT')

                @include('admin.visits._form', ['visit' => $visit])

                <div class="d-flex flex-wrap gap-2 mt-4">
                    <button type="submit" class="btn btn-primary" @disabled(!$canUpdate)>{{ __('visits.update_note') }}</button>
                    <a href="{{ route('admin.visits.index') }}" class="btn btn-outline-secondary">{{ __('common.back') }}</a>
                </div>
            </form>

            <div class="mt-2">
                @if ($canDelete)
                    <form action="{{ route('admin.visits.destroy', $visit) }}" method="POST" onsubmit="return confirm('{{ __('visits.confirm_delete') }}');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger">{{ __('common.delete') }}</button>
                    </form>
                @else
                    <button type="button" class="btn btn-outline-danger" disabled>{{ __('common.delete') }}</button>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

