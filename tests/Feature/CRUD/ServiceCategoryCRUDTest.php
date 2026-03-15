<?php

use App\Models\Clinic\ServiceCategory;
use App\Models\Clinic\MedicalSpecialty;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->specialty = MedicalSpecialty::factory()->create(['is_active' => true]);
});

describe('Service Category CRUD Operations', function () {

    describe('Index', function () {
        it('displays service categories index page', function () {
            ServiceCategory::factory(3)->create(['medical_specialty_id' => $this->specialty->id]);

            $response = $this->get(route('admin.service-categories.index'));

            $response->assertStatus(200)
                ->assertViewIs('admin.service-categories.index')
                ->assertViewHas('categories')
                ->assertViewHas('specialties');
        });

        it('can filter categories by specialty', function () {
            $specialty2 = MedicalSpecialty::factory()->create(['is_active' => true]);
            ServiceCategory::factory()->create(['medical_specialty_id' => $this->specialty->id]);
            ServiceCategory::factory()->create(['medical_specialty_id' => $specialty2->id]);

            $response = $this->get(route('admin.service-categories.index', ['medical_specialty_id' => $this->specialty->id]));

            $response->assertStatus(200)
                ->assertViewHas('categories');
        });
    });

    describe('Create', function () {
        it('displays service category create form', function () {
            $response = $this->get(route('admin.service-categories.create'));

            $response->assertStatus(200)
                ->assertViewIs('admin.service-categories.create')
                ->assertViewHas('specialties');
        });
    });

    describe('Store', function () {
        it('creates a new service category with valid data', function () {
            $data = [
                'medical_specialty_id' => $this->specialty->id,
                'name_en' => 'General Cleaning',
                'name_ar' => 'التنظيف العام',
                'description_en' => 'Basic cleaning services',
                'description_ar' => 'خدمات التنظيف الأساسية',
                'is_active' => true,
            ];

            $response = $this->post(route('admin.service-categories.store'), $data);

            $response->assertRedirect(route('admin.service-categories.index'))
                ->assertSessionHas('success');

            $this->assertDatabaseHas('service_categories', [
                'name_en' => 'General Cleaning',
                'medical_specialty_id' => $this->specialty->id,
            ]);
        });

        it('rejects category creation with invalid specialty', function () {
            $data = [
                'medical_specialty_id' => 9999,
                'name_en' => 'General Cleaning',
                'name_ar' => 'التنظيف العام',
            ];

            $response = $this->post(route('admin.service-categories.store'), $data);

            $response->assertSessionHasErrors('medical_specialty_id');
        });

        it('rejects category creation with missing required fields', function () {
            $data = [
                'medical_specialty_id' => $this->specialty->id,
            ];

            $response = $this->post(route('admin.service-categories.store'), $data);

            $response->assertSessionHasErrors(['name_en', 'name_ar']);
        });
    });

    describe('Edit', function () {
        it('displays service category edit form', function () {
            $category = ServiceCategory::factory()->create(['medical_specialty_id' => $this->specialty->id]);

            $response = $this->get(route('admin.service-categories.edit', $category));

            $response->assertStatus(200)
                ->assertViewIs('admin.service-categories.edit')
                ->assertViewHas('category')
                ->assertViewHas('specialties');
        });
    });

    describe('Update', function () {
        it('updates service category with valid data', function () {
            $category = ServiceCategory::factory()->create(['medical_specialty_id' => $this->specialty->id]);

            $data = [
                'medical_specialty_id' => $this->specialty->id,
                'name_en' => 'Updated Category',
                'name_ar' => 'الفئة المحدثة',
                'description_en' => 'Updated description',
                'description_ar' => 'وصف محدث',
                'is_active' => true,
            ];

            $response = $this->put(route('admin.service-categories.update', $category), $data);

            $response->assertRedirect(route('admin.service-categories.index'))
                ->assertSessionHas('success');

            $this->assertDatabaseHas('service_categories', [
                'id' => $category->id,
                'name_en' => 'Updated Category',
            ]);
        });

        it('persists category changes to database', function () {
            $category = ServiceCategory::factory()->create([
                'medical_specialty_id' => $this->specialty->id,
                'name_en' => 'Original Name',
            ]);

            $this->put(route('admin.service-categories.update', $category), [
                'medical_specialty_id' => $this->specialty->id,
                'name_en' => 'Changed Name',
                'name_ar' => $category->name_ar,
                'is_active' => true,
            ]);

            $this->assertDatabaseHas('service_categories', [
                'id' => $category->id,
                'name_en' => 'Changed Name',
            ]);
        });

        it('rejects update with invalid specialty', function () {
            $category = ServiceCategory::factory()->create(['medical_specialty_id' => $this->specialty->id]);

            $data = [
                'medical_specialty_id' => 9999,
                'name_en' => 'Updated Category',
                'name_ar' => 'الفئة المحدثة',
            ];

            $response = $this->put(route('admin.service-categories.update', $category), $data);

            $response->assertSessionHasErrors('medical_specialty_id');
        });
    });

    describe('Delete', function () {
        it('deletes service category record', function () {
            $category = ServiceCategory::factory()->create(['medical_specialty_id' => $this->specialty->id]);
            $categoryId = $category->id;

            $response = $this->delete(route('admin.service-categories.destroy', $category));

            $response->assertRedirect(route('admin.service-categories.index'))
                ->assertSessionHas('success');

            $this->assertDatabaseMissing('service_categories', [
                'id' => $categoryId,
            ]);
        });

        it('prevents deletion of category with services', function () {
            $category = ServiceCategory::factory()->create(['medical_specialty_id' => $this->specialty->id]);
            $category->services()->create([
                'name_en' => 'Service',
                'name_ar' => 'خدمة',
                'base_price' => 100,
            ]);

            $response = $this->delete(route('admin.service-categories.destroy', $category));

            $response->assertSessionHas('error');
            $this->assertDatabaseHas('service_categories', [
                'id' => $category->id,
            ]);
        });

        it('completely removes empty category from database', function () {
            $category = ServiceCategory::factory()->create(['medical_specialty_id' => $this->specialty->id]);

            $this->delete(route('admin.service-categories.destroy', $category));

            $this->assertDatabaseCount('service_categories', 0);
        });
    });
});

