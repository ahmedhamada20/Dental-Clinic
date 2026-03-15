<?php

use App\Models\Billing\Promotion;
use App\Models\Clinic\Service;
use App\Models\Clinic\ServiceCategory;
use App\Models\Clinic\MedicalSpecialty;
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

describe('Promotion CRUD Operations', function () {

    describe('Index', function () {
        it('displays promotions index page', function () {
            Promotion::factory(3)->create();

            $response = $this->get(route('admin.promotions.index'));

            $response->assertStatus(200)
                ->assertViewIs('admin.promotions.index')
                ->assertViewHas('promotions');
        });

        it('loads promotions with correct counts', function () {
            Promotion::factory(2)->create();

            $response = $this->get(route('admin.promotions.index'));

            $response->assertStatus(200)
                ->assertViewHas('promotions');
        });
    });

    describe('Create', function () {
        it('displays promotion create form', function () {
            $response = $this->get(route('admin.promotions.create'));

            $response->assertStatus(200)
                ->assertViewIs('admin.promotions.create')
                ->assertViewHas('services')
                ->assertViewHas('promotionTypes');
        });

        it('loads active services for form', function () {
            Service::factory()->create([
                'service_category_id' => $this->category->id,
                'is_active' => true,
            ]);

            $response = $this->get(route('admin.promotions.create'));

            $response->assertStatus(200)
                ->assertViewHas('services');
        });
    });

    describe('Store', function () {
        it('creates a new promotion with valid data', function () {
            $service = Service::factory()->create(['service_category_id' => $this->category->id]);

            $data = [
                'name' => 'Summer Discount',
                'description' => 'Special summer promotion',
                'type' => 'percentage',
                'discount_value' => 15,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addMonth()->toDateString(),
                'is_active' => true,
                'service_ids' => [$service->id],
            ];

            $response = $this->post(route('admin.promotions.store'), $data);

            $response->assertRedirect(route('admin.promotions.index'))
                ->assertSessionHas('success');

            $this->assertDatabaseHas('promotions', [
                'name' => 'Summer Discount',
                'type' => 'percentage',
            ]);
        });

        it('rejects promotion with invalid discount type', function () {
            $data = [
                'name' => 'Summer Discount',
                'type' => 'invalid_type',
                'discount_value' => 15,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addMonth()->toDateString(),
            ];

            $response = $this->post(route('admin.promotions.store'), $data);

            $response->assertSessionHasErrors('type');
        });

        it('rejects promotion with negative discount', function () {
            $data = [
                'name' => 'Summer Discount',
                'type' => 'percentage',
                'discount_value' => -10,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addMonth()->toDateString(),
            ];

            $response = $this->post(route('admin.promotions.store'), $data);

            $response->assertSessionHasErrors('discount_value');
        });

        it('rejects promotion with end date before start date', function () {
            $data = [
                'name' => 'Summer Discount',
                'type' => 'percentage',
                'discount_value' => 15,
                'start_date' => now()->addMonth()->toDateString(),
                'end_date' => now()->toDateString(),
            ];

            $response = $this->post(route('admin.promotions.store'), $data);

            $response->assertSessionHasErrors('end_date');
        });

        it('rejects promotion with missing required fields', function () {
            $data = [
                'name' => 'Summer Discount',
            ];

            $response = $this->post(route('admin.promotions.store'), $data);

            $response->assertSessionHasErrors(['type', 'discount_value', 'start_date', 'end_date']);
        });
    });

    describe('Show', function () {
        it('displays promotion details page', function () {
            $promotion = Promotion::factory()->create();

            $response = $this->get(route('admin.promotions.show', $promotion));

            $response->assertStatus(200)
                ->assertViewIs('admin.promotions.show')
                ->assertViewHas('promotion');
        });

        it('returns 404 for non-existent promotion', function () {
            $response = $this->get(route('admin.promotions.show', 9999));

            $response->assertStatus(404);
        });
    });

    describe('Edit', function () {
        it('displays promotion edit form', function () {
            $promotion = Promotion::factory()->create();

            $response = $this->get(route('admin.promotions.edit', $promotion));

            $response->assertStatus(200)
                ->assertViewIs('admin.promotions.edit')
                ->assertViewHas('promotion')
                ->assertViewHas('services')
                ->assertViewHas('promotionTypes');
        });
    });

    describe('Update', function () {
        it('updates promotion with valid data', function () {
            $promotion = Promotion::factory()->create();
            $service = Service::factory()->create(['service_category_id' => $this->category->id]);

            $data = [
                'name' => 'Updated Promotion',
                'description' => 'Updated description',
                'type' => 'fixed',
                'discount_value' => 50,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addMonth()->toDateString(),
                'is_active' => true,
                'service_ids' => [$service->id],
            ];

            $response = $this->put(route('admin.promotions.update', $promotion), $data);

            $response->assertRedirect(route('admin.promotions.index'))
                ->assertSessionHas('success');

            $this->assertDatabaseHas('promotions', [
                'id' => $promotion->id,
                'name' => 'Updated Promotion',
                'discount_value' => 50,
            ]);
        });

        it('persists promotion changes to database', function () {
            $promotion = Promotion::factory()->create(['name' => 'Original Name']);

            $this->put(route('admin.promotions.update', $promotion), [
                'name' => 'Changed Name',
                'type' => $promotion->type,
                'discount_value' => $promotion->discount_value,
                'start_date' => $promotion->start_date,
                'end_date' => $promotion->end_date,
                'is_active' => true,
                'service_ids' => [],
            ]);

            $this->assertDatabaseHas('promotions', [
                'id' => $promotion->id,
                'name' => 'Changed Name',
            ]);
        });

        it('rejects update with invalid discount type', function () {
            $promotion = Promotion::factory()->create();

            $data = [
                'name' => 'Updated Promotion',
                'type' => 'invalid_type',
                'discount_value' => 15,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addMonth()->toDateString(),
            ];

            $response = $this->put(route('admin.promotions.update', $promotion), $data);

            $response->assertSessionHasErrors('type');
        });
    });

    describe('Delete', function () {
        it('deletes promotion record', function () {
            $promotion = Promotion::factory()->create();
            $promotionId = $promotion->id;

            $response = $this->delete(route('admin.promotions.destroy', $promotion));

            $response->assertRedirect(route('admin.promotions.index'))
                ->assertSessionHas('success');

            $this->assertDatabaseMissing('promotions', [
                'id' => $promotionId,
            ]);
        });

        it('completely removes promotion from database', function () {
            Promotion::factory()->create();

            $promotion = Promotion::first();
            $this->delete(route('admin.promotions.destroy', $promotion));

            $this->assertDatabaseCount('promotions', 0);
        });
    });
});

