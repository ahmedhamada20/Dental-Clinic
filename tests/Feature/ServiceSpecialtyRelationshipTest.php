<?php

use App\Models\Clinic\MedicalSpecialty;
use App\Models\Clinic\Service;
use App\Models\Clinic\ServiceCategory;
use Database\Seeders\Phase1\MedicalSpecialtySeeder;
use Database\Seeders\Phase1\ServiceCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

it('links specialties to their categories and services', function () {
    $this->seed([
        MedicalSpecialtySeeder::class,
        ServiceCatalogSeeder::class,
    ]);

    $dental = MedicalSpecialty::query()->where('name', 'Dental')->firstOrFail();
    $dermatology = MedicalSpecialty::query()->where('name', 'Dermatology')->firstOrFail();
    $ophthalmology = MedicalSpecialty::query()->where('name', 'Ophthalmology')->firstOrFail();

    expect($dental->serviceCategories()->orderBy('sort_order')->pluck('name_en')->all())
        ->toBe(['Cleaning', 'Root Canal'])
        ->and($dermatology->serviceCategories()->orderBy('sort_order')->pluck('name_en')->all())
        ->toBe(['Skin Consultation', 'Laser Session'])
        ->and($ophthalmology->serviceCategories()->orderBy('sort_order')->pluck('name_en')->all())
        ->toBe(['Eye Examination', 'Vision Test']);

    expect(
        Service::query()
            ->whereHas('category', fn ($query) => $query->where('medical_specialty_id', $dental->id))
            ->orderBy('sort_order')
            ->pluck('name_en')
            ->all()
    )->toBe(['Cleaning', 'Root Canal']);
});

it('allows the same category name across different specialties', function () {
    $specialtyA = MedicalSpecialty::query()->create([
        'name' => 'Specialty A',
        'description' => 'A',
        'is_active' => true,
    ]);

    $specialtyB = MedicalSpecialty::query()->create([
        'name' => 'Specialty B',
        'description' => 'B',
        'is_active' => true,
    ]);

    ServiceCategory::query()->create([
        'medical_specialty_id' => $specialtyA->id,
        'name_ar' => 'استشارة',
        'name_en' => 'Consultation',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    ServiceCategory::query()->create([
        'medical_specialty_id' => $specialtyB->id,
        'name_ar' => 'استشارة',
        'name_en' => 'Consultation',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    expect(ServiceCategory::query()->where('name_en', 'Consultation')->count())->toBe(2);
});

it('rejects duplicate category names within the same specialty', function () {
    $specialty = MedicalSpecialty::query()->create([
        'name' => 'Shared Specialty',
        'description' => 'Shared specialty description',
        'is_active' => true,
    ]);

    ServiceCategory::query()->create([
        'medical_specialty_id' => $specialty->id,
        'name_ar' => 'استشارة',
        'name_en' => 'Consultation',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $request = new \App\Modules\Settings\Requests\StoreServiceCategoryRequest();
    $request->merge([
        'medical_specialty_id' => $specialty->id,
        'name_ar' => 'استشارة ثانية',
        'name_en' => 'Consultation',
    ]);

    $validator = Validator::make(
        $request->all(),
        $request->rules(),
        $request->messages()
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('name_en'))
        ->toBe('This service category already exists for the selected specialty.');
});

