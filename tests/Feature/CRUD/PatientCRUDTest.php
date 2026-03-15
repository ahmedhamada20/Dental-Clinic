<?php

use App\Models\Patient\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create an authenticated user with necessary permissions
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

describe('Patient CRUD Operations', function () {

    describe('Index', function () {
        it('displays patients index page', function () {
            Patient::factory(3)->create();

            $response = $this->get(route('admin.patients.index'));

            $response->assertStatus(200)
                ->assertViewIs('admin.patients.index')
                ->assertViewHas('patients')
                ->assertViewHas('summary')
                ->assertViewHas('statuses');
        });

        it('can search patients by name', function () {
            $patient = Patient::factory()->create(['first_name' => 'Ahmed']);
            Patient::factory()->create(['first_name' => 'Mohamed']);

            $response = $this->get(route('admin.patients.index', ['search' => 'Ahmed']));

            $response->assertStatus(200);
            $this->assertDatabaseHas('patients', ['first_name' => 'Ahmed']);
        });

        it('can filter patients by status', function () {
            Patient::factory()->create(['status' => 'active']);
            Patient::factory()->create(['status' => 'inactive']);

            $response = $this->get(route('admin.patients.index', ['status' => 'active']));

            $response->assertStatus(200)
                ->assertViewHas('patients');
        });
    });

    describe('Create', function () {
        it('displays patient create form', function () {
            $response = $this->get(route('admin.patients.create'));

            $response->assertStatus(200)
                ->assertViewIs('admin.patients.create')
                ->assertViewHas('statuses')
                ->assertViewHas('fileCategories')
                ->assertViewHas('patient');
        });
    });

    describe('Store', function () {
        it('creates a new patient with valid data', function () {
            $data = [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'phone' => '01012345678',
                'email' => 'john@example.com',
                'date_of_birth' => '1990-01-15',
                'gender' => 'male',
                'address' => '123 Main St',
                'city' => 'Cairo',
                'status' => 'active',
            ];

            $response = $this->post(route('admin.patients.store'), $data);

            $response->assertRedirect(route('admin.patients.index'))
                ->assertSessionHas('success');

            $this->assertDatabaseHas('patients', [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'phone' => '01012345678',
                'email' => 'john@example.com',
            ]);
        });

        it('rejects patient creation with invalid email', function () {
            $data = [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'phone' => '01012345678',
                'email' => 'invalid-email',
                'date_of_birth' => '1990-01-15',
                'gender' => 'male',
                'address' => '123 Main St',
                'city' => 'Cairo',
                'status' => 'active',
            ];

            $response = $this->post(route('admin.patients.store'), $data);

            $response->assertSessionHasErrors('email');
        });

        it('rejects patient creation with invalid phone', function () {
            $data = [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'phone' => 'invalid',
                'email' => 'john@example.com',
                'date_of_birth' => '1990-01-15',
                'gender' => 'male',
                'address' => '123 Main St',
                'city' => 'Cairo',
                'status' => 'active',
            ];

            $response = $this->post(route('admin.patients.store'), $data);

            $response->assertSessionHasErrors('phone');
        });

        it('rejects patient creation with missing required fields', function () {
            $data = [
                'first_name' => 'John',
                // Missing last_name and phone
            ];

            $response = $this->post(route('admin.patients.store'), $data);

            $response->assertSessionHasErrors(['last_name', 'phone']);
        });
    });

    describe('Show', function () {
        it('displays patient details page', function () {
            $patient = Patient::factory()->create();

            $response = $this->get(route('admin.patients.show', $patient));

            $response->assertStatus(200)
                ->assertViewIs('admin.patients.show')
                ->assertViewHas('patient');
        });

        it('returns 404 for non-existent patient', function () {
            $response = $this->get(route('admin.patients.show', 9999));

            $response->assertStatus(404);
        });
    });

    describe('Edit', function () {
        it('displays patient edit form', function () {
            $patient = Patient::factory()->create();

            $response = $this->get(route('admin.patients.edit', $patient));

            $response->assertStatus(200)
                ->assertViewIs('admin.patients.edit')
                ->assertViewHas('patient');
        });
    });

    describe('Update', function () {
        it('updates patient with valid data', function () {
            $patient = Patient::factory()->create();

            $data = [
                'first_name' => 'Updated',
                'last_name' => 'Name',
                'phone' => '01098765432',
                'email' => 'updated@example.com',
                'date_of_birth' => '1990-01-15',
                'gender' => 'female',
                'address' => 'New Address',
                'city' => 'Alexandria',
                'status' => 'active',
            ];

            $response = $this->put(route('admin.patients.update', $patient), $data);

            $response->assertRedirect(route('admin.patients.show', $patient))
                ->assertSessionHas('success');

            $this->assertDatabaseHas('patients', [
                'id' => $patient->id,
                'first_name' => 'Updated',
                'phone' => '01098765432',
            ]);
        });

        it('rejects update with invalid email', function () {
            $patient = Patient::factory()->create();

            $data = [
                'first_name' => 'Updated',
                'last_name' => 'Name',
                'phone' => '01098765432',
                'email' => 'invalid-email',
                'date_of_birth' => '1990-01-15',
                'gender' => 'female',
                'address' => 'New Address',
                'city' => 'Alexandria',
                'status' => 'active',
            ];

            $response = $this->put(route('admin.patients.update', $patient), $data);

            $response->assertSessionHasErrors('email');
        });

        it('persists changes to database', function () {
            $patient = Patient::factory()->create([
                'first_name' => 'Original',
                'phone' => '01012345678',
            ]);

            $this->put(route('admin.patients.update', $patient), [
                'first_name' => 'Changed',
                'last_name' => $patient->last_name,
                'phone' => '01098765432',
                'email' => $patient->email,
                'date_of_birth' => $patient->date_of_birth,
                'gender' => $patient->gender,
                'address' => $patient->address,
                'city' => $patient->city,
                'status' => 'active',
            ]);

            $this->assertDatabaseHas('patients', [
                'id' => $patient->id,
                'first_name' => 'Changed',
                'phone' => '01098765432',
            ]);
        });
    });

    describe('Delete', function () {
        it('deletes patient record', function () {
            $patient = Patient::factory()->create();
            $patientId = $patient->id;

            $response = $this->delete(route('admin.patients.destroy', $patient));

            $response->assertRedirect(route('admin.patients.index'))
                ->assertSessionHas('success');

            $this->assertDatabaseMissing('patients', [
                'id' => $patientId,
            ]);
        });

        it('removes patient from database completely', function () {
            $patient = Patient::factory()->create();

            $this->delete(route('admin.patients.destroy', $patient));

            $this->assertDatabaseCount('patients', 0);
        });
    });
});

