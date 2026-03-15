<?php

use App\Support\SpecialtyModuleRegistry;
use Illuminate\Support\Facades\Route;

it('registers specialty modules separately from the generic core', function () {
    $registry = app(SpecialtyModuleRegistry::class);
    $modules = $registry->all();

    expect($modules)->toHaveCount(1)
        ->and($modules->first()->key)->toBe('dental')
        ->and($modules->first()->supportsSpecialty('dental'))->toBeTrue()
        ->and($modules->first()->supportsSpecialty('dermatology'))->toBeFalse();
});

it('loads dental routes through the optional module registry instead of the core route file', function () {
    expect(Route::has('admin.visits.odontogram.store'))->toBeTrue()
        ->and(Route::has('admin.visits.odontogram-history.index'))->toBeTrue();

    $storeRoute = Route::getRoutes()->getByName('admin.visits.odontogram.store');
    $historyRoute = Route::getRoutes()->getByName('admin.visits.odontogram-history.index');

    expect($storeRoute?->uri())->toBe('visits/{visit}/odontogram')
        ->and($historyRoute?->uri())->toBe('visits/{visit}/odontogram/history');
});

