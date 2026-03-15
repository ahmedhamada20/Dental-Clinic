<?php

use App\Models\Appointment\WaitingListRequest;
use App\Models\Clinic\MedicalSpecialty;
use App\Models\Clinic\Service;
use App\Models\Clinic\ServiceCategory;
use App\Models\Patient\Patient;
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
    $this->service = Service::factory()->create(['service_category_id' => $this->category->id]);
});

describe('Waiting List CRUD Operations', function () {

    describe('Index', function () {
        it('displays waiting list index page', function () {
            WaitingListRequest::factory(3)->create();

            $response = $this->get(route('admin.waiting-list.index'));

            $response->assertStatus(200)
                ->assertViewIs('admin.waiting-list.index')
                ->assertViewHas('waitingListRequests')
                ->assertViewHas('statuses')
                ->assertViewHas('stats');
        });

        it('can filter waiting list by status', function () {
            WaitingListRequest::factory()->create(['status' => 'pending']);
            WaitingListRequest::factory()->create(['status' => 'fulfilled']);

            $response = $this->get(route('admin.waiting-list.index', ['status' => 'pending']));

            $response->assertStatus(200)
                ->assertViewHas('waitingListRequests');
        });

        it('can filter waiting list by date range', function () {
            $date = now()->toDateString();
            WaitingListRequest::factory()->create(['preferred_date' => $date]);

            $response = $this->get(route('admin.waiting-list.index', [
                'date_from' => $date,
                'date_to' => $date,
            ]));

            $response->assertStatus(200)
                ->assertViewHas('waitingListRequests');
        });

        it('can search waiting list by patient name', function () {
            $patient = Patient::factory()->create(['first_name' => 'Ahmed']);
            WaitingListRequest::factory()->create(['patient_id' => $patient->id]);

            $response = $this->get(route('admin.waiting-list.index', ['search' => 'Ahmed']));

            $response->assertStatus(200)
                ->assertViewHas('waitingListRequests');
        });
    });

    describe('Create', function () {
        it('displays waiting list create form', function () {
            $response = $this->get(route('admin.waiting-list.create'));

            $response->assertStatus(200)
                ->assertViewIs('admin.waiting-list.create')
                ->assertViewHas('patients')
                ->assertViewHas('specialties');
        });
    });

    describe('Store', function () {
        it('creates a new waiting list request with valid data', function () {
            $patient = Patient::factory()->create();

            $data = [
                'patient_id' => $patient->id,
                'service_id' => $this->service->id,
                'preferred_date' => now()->addDay()->toDateString(),
                'time_preference' => 'morning',
                'notes' => 'First appointment preferred',
            ];

            $response = $this->post(route('admin.waiting-list.store'), $data);

            $response->assertRedirect(route('admin.waiting-list.index'))
                ->assertSessionHas('success');

            $this->assertDatabaseHas('waiting_list_requests', [
                'patient_id' => $patient->id,
                'service_id' => $this->service->id,
            ]);
        });

        it('sets default status as pending', function () {
            $patient = Patient::factory()->create();

            $this->post(route('admin.waiting-list.store'), [
                'patient_id' => $patient->id,
                'service_id' => $this->service->id,
                'preferred_date' => now()->addDay()->toDateString(),
                'time_preference' => 'morning',
            ]);

            $this->assertDatabaseHas('waiting_list_requests', [
                'patient_id' => $patient->id,
                'status' => 'pending',
            ]);
        });

        it('rejects waiting list with invalid patient', function () {
            $data = [
                'patient_id' => 9999,
                'service_id' => $this->service->id,
                'preferred_date' => now()->addDay()->toDateString(),
            ];

            $response = $this->post(route('admin.waiting-list.store'), $data);

            $response->assertSessionHasErrors('patient_id');
        });

        it('rejects waiting list with past date', function () {
            $patient = Patient::factory()->create();

            $data = [
                'patient_id' => $patient->id,
                'service_id' => $this->service->id,
                'preferred_date' => now()->subDay()->toDateString(),
            ];

            $response = $this->post(route('admin.waiting-list.store'), $data);

            $response->assertSessionHasErrors('preferred_date');
        });

        it('rejects waiting list with missing required fields', function () {
            $data = [
                'service_id' => $this->service->id,
            ];

            $response = $this->post(route('admin.waiting-list.store'), $data);

            $response->assertSessionHasErrors(['patient_id', 'preferred_date']);
        });
    });

    describe('Show', function () {
        it('displays waiting list request details', function () {
            $waitingList = WaitingListRequest::factory()->create();

            $response = $this->get(route('admin.waiting-list.show', $waitingList));

            $response->assertStatus(200)
                ->assertViewIs('admin.waiting-list.show')
                ->assertViewHas('waitingListRequest');
        });

        it('returns 404 for non-existent waiting list', function () {
            $response = $this->get(route('admin.waiting-list.show', 9999));

            $response->assertStatus(404);
        });
    });

    describe('Delete', function () {
        it('deletes waiting list request', function () {
            $waitingList = WaitingListRequest::factory()->create();
            $waitingListId = $waitingList->id;

            $response = $this->delete(route('admin.waiting-list.destroy', $waitingList));

            $response->assertRedirect(route('admin.waiting-list.index'))
                ->assertSessionHas('success');

            $this->assertDatabaseMissing('waiting_list_requests', [
                'id' => $waitingListId,
            ]);
        });

        it('completely removes waiting list from database', function () {
            WaitingListRequest::factory()->create();

            $waitingList = WaitingListRequest::first();
            $this->delete(route('admin.waiting-list.destroy', $waitingList));

            $this->assertDatabaseCount('waiting_list_requests', 0);
        });

        it('removes only specified waiting list request', function () {
            $wl1 = WaitingListRequest::factory()->create();
            $wl2 = WaitingListRequest::factory()->create();

            $this->delete(route('admin.waiting-list.destroy', $wl1));

            $this->assertDatabaseMissing('waiting_list_requests', [
                'id' => $wl1->id,
            ]);
            $this->assertDatabaseHas('waiting_list_requests', [
                'id' => $wl2->id,
            ]);
        });
    });
});

