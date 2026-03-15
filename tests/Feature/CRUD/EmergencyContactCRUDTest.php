<?php

use App\Models\Patient\EmergencyContact;
use App\Models\Patient\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->patient = Patient::factory()->create();
});

describe('Emergency Contact CRUD Operations', function () {

    describe('Store', function () {
        it('creates a new emergency contact with valid data', function () {
            $data = [
                'name' => 'John Smith',
                'relation' => 'Brother',
                'phone' => '01012345678',
                'notes' => 'Lives nearby',
            ];

            $response = $this->post(
                route('admin.patients.emergency-contacts.store', $this->patient),
                $data
            );

            $response->assertRedirect(route('admin.patients.show', [
                'patient' => $this->patient->id,
                'tab' => 'contacts'
            ]))
                ->assertSessionHas('success');

            $this->assertDatabaseHas('emergency_contacts', [
                'patient_id' => $this->patient->id,
                'name' => 'John Smith',
                'phone' => '01012345678',
            ]);
        });

        it('rejects emergency contact with invalid phone', function () {
            $data = [
                'name' => 'John Smith',
                'relation' => 'Brother',
                'phone' => 'invalid',
                'notes' => 'Lives nearby',
            ];

            $response = $this->post(
                route('admin.patients.emergency-contacts.store', $this->patient),
                $data
            );

            $response->assertSessionHasErrors('phone');
        });

        it('rejects emergency contact with missing required fields', function () {
            $data = [
                'relation' => 'Brother',
                'notes' => 'Lives nearby',
            ];

            $response = $this->post(
                route('admin.patients.emergency-contacts.store', $this->patient),
                $data
            );

            $response->assertSessionHasErrors(['name', 'phone']);
        });

        it('stores emergency contact with optional fields', function () {
            $data = [
                'name' => 'Jane Doe',
                'relation' => null,
                'phone' => '01012345678',
                'notes' => null,
            ];

            $response = $this->post(
                route('admin.patients.emergency-contacts.store', $this->patient),
                $data
            );

            $response->assertSessionHas('success');

            $this->assertDatabaseHas('emergency_contacts', [
                'name' => 'Jane Doe',
                'phone' => '01012345678',
            ]);
        });
    });

    describe('Update', function () {
        it('updates emergency contact with valid data', function () {
            $contact = $this->patient->emergencyContacts()->create([
                'name' => 'Original Name',
                'relation' => 'Sister',
                'phone' => '01012345678',
                'notes' => 'Original notes',
            ]);

            $data = [
                'name' => 'Updated Name',
                'relation' => 'Brother',
                'phone' => '01098765432',
                'notes' => 'Updated notes',
            ];

            $response = $this->put(
                route('admin.patients.emergency-contacts.update', [$this->patient, $contact->id]),
                $data
            );

            $response->assertRedirect(route('admin.patients.show', [
                'patient' => $this->patient->id,
                'tab' => 'contacts'
            ]))
                ->assertSessionHas('success');

            $this->assertDatabaseHas('emergency_contacts', [
                'id' => $contact->id,
                'name' => 'Updated Name',
                'phone' => '01098765432',
            ]);
        });

        it('persists emergency contact changes to database', function () {
            $contact = $this->patient->emergencyContacts()->create([
                'name' => 'Original Name',
                'relation' => 'Sister',
                'phone' => '01012345678',
            ]);

            $this->put(
                route('admin.patients.emergency-contacts.update', [$this->patient, $contact->id]),
                [
                    'name' => 'Changed Name',
                    'relation' => 'Father',
                    'phone' => '01098765432',
                    'notes' => 'Updated notes',
                ]
            );

            $this->assertDatabaseHas('emergency_contacts', [
                'id' => $contact->id,
                'name' => 'Changed Name',
                'phone' => '01098765432',
                'relation' => 'Father',
            ]);
        });

        it('rejects update with invalid phone', function () {
            $contact = $this->patient->emergencyContacts()->create([
                'name' => 'John Smith',
                'phone' => '01012345678',
            ]);

            $data = [
                'name' => 'John Smith',
                'relation' => 'Brother',
                'phone' => 'invalid',
            ];

            $response = $this->put(
                route('admin.patients.emergency-contacts.update', [$this->patient, $contact->id]),
                $data
            );

            $response->assertSessionHasErrors('phone');
        });
    });

    describe('Delete', function () {
        it('deletes emergency contact record', function () {
            $contact = $this->patient->emergencyContacts()->create([
                'name' => 'John Smith',
                'phone' => '01012345678',
            ]);
            $contactId = $contact->id;

            $response = $this->delete(
                route('admin.patients.emergency-contacts.destroy', [$this->patient, $contact->id])
            );

            $response->assertRedirect(route('admin.patients.show', [
                'patient' => $this->patient->id,
                'tab' => 'contacts'
            ]))
                ->assertSessionHas('success');

            $this->assertDatabaseMissing('emergency_contacts', [
                'id' => $contactId,
            ]);
        });

        it('completely removes emergency contact from database', function () {
            $contact = $this->patient->emergencyContacts()->create([
                'name' => 'John Smith',
                'phone' => '01012345678',
            ]);

            $this->delete(
                route('admin.patients.emergency-contacts.destroy', [$this->patient, $contact->id])
            );

            $this->assertDatabaseCount('emergency_contacts', 0);
        });

        it('only removes specified contact, not all', function () {
            $contact1 = $this->patient->emergencyContacts()->create([
                'name' => 'Contact 1',
                'phone' => '01012345678',
            ]);
            $contact2 = $this->patient->emergencyContacts()->create([
                'name' => 'Contact 2',
                'phone' => '01098765432',
            ]);

            $this->delete(
                route('admin.patients.emergency-contacts.destroy', [$this->patient, $contact1->id])
            );

            $this->assertDatabaseMissing('emergency_contacts', [
                'id' => $contact1->id,
            ]);
            $this->assertDatabaseHas('emergency_contacts', [
                'id' => $contact2->id,
            ]);
        });
    });
});

