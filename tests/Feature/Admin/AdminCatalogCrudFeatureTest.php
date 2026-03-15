<?php

use App\Models\Clinic\MedicalSpecialty;
use App\Models\Clinic\Service;
use App\Models\Clinic\ServiceCategory;
use Tests\Support\AdminFeatureTestHelpers;

uses(AdminFeatureTestHelpers::class);

beforeEach(function () {
    $this->seedAdminFeaturePermissions();
    $this->admin = $this->createAdminUser([
        'specialties.view',
        'specialties.manage',
        'service-categories.view',
        'service-categories.manage',
        'services.view',
        'services.manage',
    ]);
});

it('covers medical specialties index create store edit update and activate toggles', function () {
    $this->actingAs($this->admin)->get(route('admin.specialties.index'))->assertOk();
    $this->actingAs($this->admin)->get(route('admin.specialties.create'))->assertOk();

    $this->actingAs($this->admin)
        ->post(route('admin.specialties.store'), [
            'name' => 'Orthodontics',
            'description' => 'Braces and alignment',
            'is_active' => 1,
        ])
        ->assertRedirect(route('admin.specialties.index'));

    $specialty = MedicalSpecialty::query()->where('name', 'Orthodontics')->firstOrFail();

    $doctor = $this->createDentist($specialty, [
        'specialty_id' => null,
        'email' => 'attach-doctor@example.com',
        'phone' => '7111111111',
    ]);

    $this->actingAs($this->admin)->get(route('admin.specialties.show', $specialty))->assertOk();

    $this->actingAs($this->admin)
        ->post(route('admin.specialties.doctors.attach', $specialty), [
            'doctor_id' => $doctor->id,
        ])
        ->assertRedirect(route('admin.specialties.show', $specialty));

    expect($doctor->fresh()->specialty_id)->toBe($specialty->id);

    $this->actingAs($this->admin)->get(route('admin.specialties.edit', $specialty))->assertOk();

    $this->actingAs($this->admin)
        ->put(route('admin.specialties.update', $specialty), [
            'name' => 'Orthodontics Updated',
            'description' => 'Updated text',
            'is_active' => 1,
        ])
        ->assertRedirect(route('admin.specialties.index'));

    expect($specialty->fresh()->name)->toBe('Orthodontics Updated');

    $this->actingAs($this->admin)
        ->patch(route('admin.specialties.deactivate', $specialty))
        ->assertRedirect();

    expect($specialty->fresh()->is_active)->toBeFalse();

    $this->actingAs($this->admin)
        ->patch(route('admin.specialties.activate', $specialty))
        ->assertRedirect();

    expect($specialty->fresh()->is_active)->toBeTrue();
});

it('covers service categories index create store edit update and destroy', function () {
    $specialty = MedicalSpecialty::query()->create([
        'name' => 'General Dentistry',
        'description' => 'General',
        'is_active' => true,
    ]);

    $this->actingAs($this->admin)->get(route('admin.service-categories.index'))->assertOk();
    $this->actingAs($this->admin)->get(route('admin.service-categories.create'))->assertOk();

    $this->actingAs($this->admin)
        ->post(route('admin.service-categories.store'), [
            'medical_specialty_id' => $specialty->id,
            'name_ar' => 'حشوات',
            'name_en' => 'Fillings',
            'is_active' => 1,
            'sort_order' => 5,
        ])
        ->assertRedirect(route('admin.service-categories.index'));

    $category = ServiceCategory::query()->where('name_en', 'Fillings')->firstOrFail();

    $this->actingAs($this->admin)->get(route('admin.service-categories.edit', $category))->assertOk();

    $this->actingAs($this->admin)
        ->put(route('admin.service-categories.update', $category), [
            'medical_specialty_id' => $specialty->id,
            'name_ar' => 'حشوات محدثة',
            'name_en' => 'Fillings Updated',
            'is_active' => 1,
            'sort_order' => 6,
        ])
        ->assertRedirect(route('admin.service-categories.index'));

    expect($category->fresh()->name_en)->toBe('Fillings Updated');

    $this->actingAs($this->admin)
        ->delete(route('admin.service-categories.destroy', $category))
        ->assertRedirect(route('admin.service-categories.index'));

    expect(ServiceCategory::query()->whereKey($category->id)->exists())->toBeFalse();
});

it('prevents deleting a category that still has services', function () {
    [$specialty, $category] = $this->createSpecialtyCategoryService();

    $this->actingAs($this->admin)
        ->delete(route('admin.service-categories.destroy', $category))
        ->assertRedirect();

    expect(ServiceCategory::query()->whereKey($category->id)->exists())->toBeTrue();
});

it('covers services index create store show edit update and destroy', function () {
    [, $category] = $this->createSpecialtyCategoryService();

    $this->actingAs($this->admin)->get(route('admin.services.index'))->assertOk();
    $this->actingAs($this->admin)->get(route('admin.services.create'))->assertOk();

    $this->actingAs($this->admin)
        ->post(route('admin.services.store'), [
            'category_id' => $category->id,
            'code' => 'SRV-100',
            'name_ar' => 'كشف',
            'name_en' => 'Consultation',
            'default_price' => 200,
            'duration_minutes' => 30,
            'is_bookable' => 1,
            'is_active' => 1,
            'sort_order' => 1,
        ])
        ->assertRedirect(route('admin.services.index'));

    $service = Service::query()->where('code', 'SRV-100')->firstOrFail();

    $this->actingAs($this->admin)->get(route('admin.services.show', $service))->assertOk();
    $this->actingAs($this->admin)->get(route('admin.services.edit', $service))->assertOk();

    $this->actingAs($this->admin)
        ->put(route('admin.services.update', $service), [
            'category_id' => $category->id,
            'code' => 'SRV-100',
            'name_ar' => 'كشف محدث',
            'name_en' => 'Consultation Updated',
            'default_price' => 220,
            'duration_minutes' => 40,
            'is_bookable' => 1,
            'is_active' => 1,
            'sort_order' => 2,
        ])
        ->assertRedirect(route('admin.services.index'));

    expect($service->fresh()->name_en)->toBe('Consultation Updated');

    $this->actingAs($this->admin)
        ->delete(route('admin.services.destroy', $service))
        ->assertRedirect(route('admin.services.index'));

    expect(Service::query()->whereKey($service->id)->exists())->toBeFalse();
});

