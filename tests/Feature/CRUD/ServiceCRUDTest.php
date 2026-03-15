<?php

use App\Models\Clinic\Service;
use App\Models\Clinic\MedicalSpecialty;
use App\Models\Clinic\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->specialty = MedicalSpecialty::factory()->create(['is_active' => true]);
    $this->category = ServiceCategory::factory()->create([
        'medical_specialty_id' => $this->specialty->id,
        'is_active' => true,
    ]);
});

describe('Service CRUD Operations', function () {

    describe('Index', function () {
        it('displays services index page', function () {
            Service::factory(3)->create(['service_category_id' => $this->category->id]);

            $response = $this->get(route('admin.services.index'));

            $response->assertStatus(200)
                ->assertViewIs('admin.services.index')
                ->assertViewHas('services')
                ->assertViewHas('specialties');
        });

        it('can filter services by specialty', function () {
            $specialty2 = MedicalSpecialty::factory()->create(['is_active' => true]);
            $category2 = ServiceCategory::factory()->create([
                'medical_specialty_id' => $specialty2->id,
                'is_active' => true,
            ]);

            Service::factory()->create(['service_category_id' => $this->category->id]);
            Service::factory()->create(['service_category_id' => $category2->id]);

            $response = $this->get(route('admin.services.index', ['medical_specialty_id' => $this->specialty->id]));

            $response->assertStatus(200)
                ->assertViewHas('services');
        });
    });

    describe('Create', function () {
        it('displays service create form', function () {
            $response = $this->get(route('admin.services.create'));

            $response->assertStatus(200)
                ->assertViewIs('admin.services.create')
                ->assertViewHas('specialties')
                ->assertViewHas('categories');
        });
    });

    describe('Store', function () {
        it('creates a new service with valid data', function () {
            $data = [
                'service_category_id' => $this->category->id,
                'name_en' => 'Tooth Cleaning',
                'name_ar' => 'تنظيف الأسنان',
                'description_en' => 'Professional tooth cleaning',
                'description_ar' => 'تنظيف احترافي للأسنان',
                'base_price' => 500.00,
                'duration_minutes' => 30,
                'is_active' => true,
            ];

            $response = $this->post(route('admin.services.store'), $data);

            $response->assertRedirect(route('admin.services.index'))
                ->assertSessionHas('success');

            $this->assertDatabaseHas('services', [
                'name_en' => 'Tooth Cleaning',
                'base_price' => 500.00,
            ]);
        });

        it('rejects service creation with invalid category', function () {
            $data = [
                'service_category_id' => 9999,
                'name_en' => 'Tooth Cleaning',
                'name_ar' => 'تنظيف الأسنان',
            ];

            $response = $this->post(route('admin.services.store'), $data);

            $response->assertSessionHasErrors('service_category_id');
        });

        it('rejects service creation with invalid price', function () {
            $data = [
                'service_category_id' => $this->category->id,
                'name_en' => 'Tooth Cleaning',
                'name_ar' => 'تنظيف الأسنان',
                'base_price' => -100,
            ];

            $response = $this->post(route('admin.services.store'), $data);

            $response->assertSessionHasErrors('base_price');
        });

        it('rejects service creation with missing required fields', function () {
            $data = [
                'service_category_id' => $this->category->id,
            ];

            $response = $this->post(route('admin.services.store'), $data);

            $response->assertSessionHasErrors(['name_en', 'base_price']);
        });
    });

    describe('Show', function () {
        it('displays service details page', function () {
            $service = Service::factory()->create(['service_category_id' => $this->category->id]);

            $response = $this->get(route('admin.services.show', $service));

            $response->assertStatus(200)
                ->assertViewIs('admin.services.show')
                ->assertViewHas('service');
        });

        it('returns 404 for non-existent service', function () {
            $response = $this->get(route('admin.services.show', 9999));

            $response->assertStatus(404);
        });
    });

    describe('Edit', function () {
        it('displays service edit form', function () {
            $service = Service::factory()->create(['service_category_id' => $this->category->id]);

            $response = $this->get(route('admin.services.edit', $service));

            $response->assertStatus(200)
                ->assertViewIs('admin.services.edit')
                ->assertViewHas('service')
                ->assertViewHas('categories');
        });
    });

    describe('Update', function () {
        it('updates service with valid data', function () {
            $service = Service::factory()->create(['service_category_id' => $this->category->id]);

            $data = [
                'service_category_id' => $this->category->id,
                'name_en' => 'Updated Service',
                'name_ar' => 'الخدمة المحدثة',
                'description_en' => 'Updated description',
                'description_ar' => 'وصف محدث',
                'base_price' => 750.00,
                'duration_minutes' => 45,
                'is_active' => true,
            ];

            $response = $this->put(route('admin.services.update', $service), $data);

            $response->assertRedirect(route('admin.services.index'))
                ->assertSessionHas('success');

            $this->assertDatabaseHas('services', [
                'id' => $service->id,
                'name_en' => 'Updated Service',
                'base_price' => 750.00,
            ]);
        });

        it('persists service changes to database', function () {
            $service = Service::factory()->create([
                'service_category_id' => $this->category->id,
                'base_price' => 500.00,
            ]);

            $this->put(route('admin.services.update', $service), [
                'service_category_id' => $this->category->id,
                'name_en' => $service->name_en,
                'name_ar' => $service->name_ar,
                'base_price' => 600.00,
                'duration_minutes' => 30,
                'is_active' => true,
            ]);

            $this->assertDatabaseHas('services', [
                'id' => $service->id,
                'base_price' => 600.00,
            ]);
        });

        it('rejects update with invalid price', function () {
            $service = Service::factory()->create(['service_category_id' => $this->category->id]);

            $data = [
                'service_category_id' => $this->category->id,
                'name_en' => 'Updated Service',
                'name_ar' => 'الخدمة المحدثة',
                'base_price' => -50,
                'duration_minutes' => 30,
            ];

            $response = $this->put(route('admin.services.update', $service), $data);

            $response->assertSessionHasErrors('base_price');
        });
    });

    describe('Delete', function () {
        it('deletes service record', function () {
            $service = Service::factory()->create(['service_category_id' => $this->category->id]);
            $serviceId = $service->id;

            $response = $this->delete(route('admin.services.destroy', $service));

            $response->assertRedirect(route('admin.services.index'))
                ->assertSessionHas('success');

            $this->assertDatabaseMissing('services', [
                'id' => $serviceId,
            ]);
        });

        it('completely removes service from database', function () {
            $service = Service::factory()->create(['service_category_id' => $this->category->id]);

            $this->delete(route('admin.services.destroy', $service));

            $this->assertDatabaseCount('services', 0);
        });
    });
});

