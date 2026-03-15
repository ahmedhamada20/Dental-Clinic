<?php

use App\Models\Appointment\Appointment;
use App\Models\Patient\Patient;
use App\Models\User;
use App\Models\Clinic\MedicalSpecialty;
use App\Models\Clinic\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->specialty = MedicalSpecialty::factory()->create(['is_active' => true]);
});

describe('Appointment CRUD Operations', function () {

    describe('Index', function () {
        it('displays appointments index page', function () {
            Appointment::factory(3)->create();

            $response = $this->get(route('admin.appointments.index'));

            $response->assertStatus(200)
                ->assertViewIs('admin.appointments.index')
                ->assertViewHas('appointments')
                ->assertViewHas('statuses')
                ->assertViewHas('specialties');
        });

        it('can filter appointments by status', function () {
            Appointment::factory()->create(['status' => 'confirmed']);
            Appointment::factory()->create(['status' => 'pending']);

            $response = $this->get(route('admin.appointments.index', ['status' => 'confirmed']));

            $response->assertStatus(200)
                ->assertViewHas('appointments');
        });

        it('can filter appointments by date', function () {
            $date = now()->addDay()->toDateString();
            Appointment::factory()->create(['appointment_date' => $date]);

            $response = $this->get(route('admin.appointments.index', ['date' => $date]));

            $response->assertStatus(200)
                ->assertViewHas('appointments');
        });
    });

    describe('Create', function () {
        it('displays appointment create form', function () {
            $response = $this->get(route('admin.appointments.create'));

            $response->assertStatus(200)
                ->assertViewIs('admin.appointments.create')
                ->assertViewHas('patients')
                ->assertViewHas('specialties');
        });
    });

    describe('Store', function () {
        it('creates a new appointment with valid data', function () {
            $patient = Patient::factory()->create();
            $service = Service::factory()->create(['service_category_id' => $this->specialty->id]);
            $doctor = User::factory()->create();

            $data = [
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'service_id' => $service->id,
                'specialty_id' => $this->specialty->id,
                'appointment_date' => now()->addDay()->toDateString(),
                'start_time' => '10:00',
                'end_time' => '10:30',
                'notes' => 'First visit',
                'status' => 'pending',
            ];

            $response = $this->post(route('admin.appointments.store'), $data);

            $response->assertRedirect(route('admin.appointments.index'))
                ->assertSessionHas('success');

            $this->assertDatabaseHas('appointments', [
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'appointment_date' => $data['appointment_date'],
            ]);
        });

        it('rejects appointment with invalid patient', function () {
            $data = [
                'patient_id' => 9999,
                'appointment_date' => now()->addDay()->toDateString(),
                'start_time' => '10:00',
                'end_time' => '10:30',
            ];

            $response = $this->post(route('admin.appointments.store'), $data);

            $response->assertSessionHasErrors('patient_id');
        });

        it('rejects appointment with past date', function () {
            $patient = Patient::factory()->create();

            $data = [
                'patient_id' => $patient->id,
                'appointment_date' => now()->subDay()->toDateString(),
                'start_time' => '10:00',
                'end_time' => '10:30',
            ];

            $response = $this->post(route('admin.appointments.store'), $data);

            $response->assertSessionHasErrors('appointment_date');
        });

        it('rejects appointment with invalid time range', function () {
            $patient = Patient::factory()->create();

            $data = [
                'patient_id' => $patient->id,
                'appointment_date' => now()->addDay()->toDateString(),
                'start_time' => '10:30',
                'end_time' => '10:00',
            ];

            $response = $this->post(route('admin.appointments.store'), $data);

            $response->assertSessionHasErrors();
        });
    });

    describe('Show', function () {
        it('displays appointment details page', function () {
            $appointment = Appointment::factory()->create();

            $response = $this->get(route('admin.appointments.show', $appointment));

            $response->assertStatus(200)
                ->assertViewIs('admin.appointments.show')
                ->assertViewHas('appointment');
        });

        it('returns 404 for non-existent appointment', function () {
            $response = $this->get(route('admin.appointments.show', 9999));

            $response->assertStatus(404);
        });
    });

    describe('Edit', function () {
        it('displays appointment edit form', function () {
            $appointment = Appointment::factory()->create();

            $response = $this->get(route('admin.appointments.edit', $appointment));

            $response->assertStatus(200)
                ->assertViewIs('admin.appointments.edit')
                ->assertViewHas('appointment');
        });
    });

    describe('Update', function () {
        it('updates appointment with valid data', function () {
            $appointment = Appointment::factory()->create();
            $patient = Patient::factory()->create();

            $data = [
                'patient_id' => $patient->id,
                'appointment_date' => now()->addDays(2)->toDateString(),
                'start_time' => '14:00',
                'end_time' => '14:30',
                'notes' => 'Updated notes',
                'status' => 'confirmed',
            ];

            $response = $this->put(route('admin.appointments.update', $appointment), $data);

            $response->assertRedirect(route('admin.appointments.show', $appointment))
                ->assertSessionHas('success');

            $this->assertDatabaseHas('appointments', [
                'id' => $appointment->id,
                'patient_id' => $patient->id,
                'appointment_date' => $data['appointment_date'],
            ]);
        });

        it('persists appointment changes to database', function () {
            $appointment = Appointment::factory()->create();

            $this->put(route('admin.appointments.update', $appointment), [
                'patient_id' => $appointment->patient_id,
                'appointment_date' => now()->addDays(5)->toDateString(),
                'start_time' => '15:00',
                'end_time' => '15:30',
                'notes' => 'Rescheduled',
                'status' => 'pending',
            ]);

            $this->assertDatabaseHas('appointments', [
                'id' => $appointment->id,
                'notes' => 'Rescheduled',
            ]);
        });

        it('rejects update with invalid patient', function () {
            $appointment = Appointment::factory()->create();

            $data = [
                'patient_id' => 9999,
                'appointment_date' => now()->addDay()->toDateString(),
                'start_time' => '10:00',
                'end_time' => '10:30',
            ];

            $response = $this->put(route('admin.appointments.update', $appointment), $data);

            $response->assertSessionHasErrors('patient_id');
        });
    });

    describe('Delete', function () {
        it('deletes appointment record', function () {
            $appointment = Appointment::factory()->create();
            $appointmentId = $appointment->id;

            $response = $this->delete(route('admin.appointments.destroy', $appointment));

            $response->assertRedirect(route('admin.appointments.index'))
                ->assertSessionHas('success');

            $this->assertDatabaseMissing('appointments', [
                'id' => $appointmentId,
            ]);
        });

        it('completely removes appointment from database', function () {
            Appointment::factory()->create();

            $appointment = Appointment::first();
            $this->delete(route('admin.appointments.destroy', $appointment));

            $this->assertDatabaseCount('appointments', 0);
        });
    });
});

