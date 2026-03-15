<?php

use App\Models\Patient\Patient;
use Tests\Support\AdminFeatureTestHelpers;

uses(AdminFeatureTestHelpers::class);

beforeEach(function () {
    $this->seedAdminFeaturePermissions();
    $this->admin = $this->createAdminUser([
        'patients.view',
        'patients.create',
        'patients.edit',
        'patients.delete',
        'patients.manage-medical-history',
    ]);
});

describe('PHASE 5: Patient Module Comprehensive Testing', function () {

    describe('1. Patient Index', function () {
        it('lists all patients with pagination', function () {
            // Create 5 test patients
            for ($i = 0; $i < 5; $i++) {
                $this->createPatient();
            }

            $response = $this->actingAs($this->admin)
                ->getJson(route('api.v1.admin.patients.index'));

            expect($response->status())->toBe(200);
            expect($response->json('success'))->toBeTrue();
            expect($response->json('data.data'))->toBeArray();
            expect(count($response->json('data.data')))->toBeGreaterThanOrEqual(5);
        });

        it('filters patients by search term', function () {
            $this->createPatient(['first_name' => 'UniqueSearchName']);
            $this->createPatient(['first_name' => 'AnotherPatient']);

            $response = $this->actingAs($this->admin)
                ->getJson(route('api.v1.admin.patients.index', ['search' => 'UniqueSearch']));

            expect($response->status())->toBe(200);
            $data = $response->json('data.data');
            expect(count($data))->toBeGreaterThanOrEqual(1);
        });

        it('filters patients by status', function () {
            $this->createPatient(['status' => 'active']);
            $this->createPatient(['status' => 'inactive']);

            $response = $this->actingAs($this->admin)
                ->getJson(route('api.v1.admin.patients.index', ['status' => 'active']));

            expect($response->status())->toBe(200);
            $data = $response->json('data.data');
            foreach ($data as $patient) {
                expect($patient['status'])->toBe('active');
            }
        });
    });

    describe('2. Create Patient (POST)', function () {
        it('creates a new patient with valid data', function () {
            $payload = [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe' . time() . '@example.com',
                'phone' => '01' . random_int(100000000, 999999999),
                'gender' => 'male',
                'date_of_birth' => now()->subYears(30)->toDateString(),
                'address' => '123 Main St',
                'city' => 'Cairo',
                'status' => 'active',
            ];

            $response = $this->actingAs($this->admin)
                ->postJson(route('api.v1.admin.patients.store'), $payload);

            expect($response->status())->toBe(201);
            expect($response->json('success'))->toBeTrue();
            expect($response->json('data.id'))->toBeGreaterThan(0);
            expect($response->json('data.first_name'))->toBe('John');
            expect($response->json('data.last_name'))->toBe('Doe');
            expect($response->json('data.full_name'))->toContain('John');

            // Verify patient is in database
            $patient = Patient::find($response->json('data.id'));
            expect($patient)->not->toBeNull();
        });

        it('validates required fields on create', function () {
            $payload = [
                'first_name' => 'John',
                // Missing required fields
            ];

            $response = $this->actingAs($this->admin)
                ->postJson(route('api.v1.admin.patients.store'), $payload);

            expect($response->status())->toBe(422);
        });

        it('enforces unique email on create', function () {
            $existingEmail = 'existing' . time() . '@example.com';
            $this->createPatient(['email' => $existingEmail]);

            $payload = [
                'first_name' => 'Another',
                'last_name' => 'Patient',
                'email' => $existingEmail,
                'phone' => '01' . random_int(100000000, 999999999),
                'gender' => 'female',
                'date_of_birth' => now()->subYears(25)->toDateString(),
                'address' => 'Test',
                'city' => 'Cairo',
            ];

            $response = $this->actingAs($this->admin)
                ->postJson(route('api.v1.admin.patients.store'), $payload);

            expect($response->status())->toBe(422);
        });

        it('enforces unique phone on create', function () {
            $existingPhone = '01' . random_int(100000000, 999999999);
            $this->createPatient(['phone' => $existingPhone]);

            $payload = [
                'first_name' => 'Another',
                'last_name' => 'Patient',
                'email' => 'another' . time() . '@example.com',
                'phone' => $existingPhone,
                'gender' => 'female',
                'date_of_birth' => now()->subYears(25)->toDateString(),
                'address' => 'Test',
                'city' => 'Cairo',
            ];

            $response = $this->actingAs($this->admin)
                ->postJson(route('api.v1.admin.patients.store'), $payload);

            expect($response->status())->toBe(422);
        });
    });

    describe('3. Show Patient (GET)', function () {
        it('retrieves a patient by id', function () {
            $patient = $this->createPatient();

            $response = $this->actingAs($this->admin)
                ->getJson(route('api.v1.admin.patients.show', $patient));

            expect($response->status())->toBe(200);
            expect($response->json('data.id'))->toBe($patient->id);
            expect($response->json('data.first_name'))->toBe($patient->first_name);
        });

        it('loads patient relationships', function () {
            $patient = $this->createPatient();

            $response = $this->actingAs($this->admin)
                ->getJson(route('api.v1.admin.patients.show', $patient));

            expect($response->status())->toBe(200);
            expect($response->json('data'))->toHaveKey('profile');
            expect($response->json('data'))->toHaveKey('medical_history');
            expect($response->json('data'))->toHaveKey('emergency_contacts');
        });

        it('returns 404 for non-existent patient', function () {
            $response = $this->actingAs($this->admin)
                ->getJson(route('api.v1.admin.patients.show', 99999));

            expect($response->status())->toBe(404);
        });
    });

    describe('4. Update Patient (PUT)', function () {
        it('updates patient with valid data', function () {
            $patient = $this->createPatient(['first_name' => 'Original']);

            $updatePayload = [
                'first_name' => 'Updated',
                'last_name' => 'Name',
                'email' => $patient->email,
                'phone' => $patient->phone,
                'gender' => 'female',
                'date_of_birth' => now()->subYears(28)->toDateString(),
                'address' => 'New Address',
                'city' => 'Giza',
                'status' => 'active',
            ];

            $response = $this->actingAs($this->admin)
                ->putJson(route('api.v1.admin.patients.update', $patient), $updatePayload);

            expect($response->status())->toBe(200);
            expect($response->json('success'))->toBeTrue();
            expect($response->json('data.first_name'))->toBe('Updated');
            expect($response->json('data.address'))->toBe('New Address');

            // Verify in database
            $patient->refresh();
            expect($patient->first_name)->toBe('Updated');
            expect($patient->full_name)->toContain('Updated');
        });

        it('handles partial updates', function () {
            $patient = $this->createPatient(['address' => 'Original Address']);

            $updatePayload = [
                'first_name' => 'PartialUpdate',
            ];

            $response = $this->actingAs($this->admin)
                ->putJson(route('api.v1.admin.patients.update', $patient), $updatePayload);

            expect($response->status())->toBe(200);
            expect($response->json('data.first_name'))->toBe('PartialUpdate');

            // Original values should remain
            $patient->refresh();
            expect($patient->address)->toBe('Original Address');
        });

        it('allows updating email if unique among other patients', function () {
            $patient = $this->createPatient();
            $newEmail = 'newemail' . time() . '@example.com';

            $updatePayload = [
                'email' => $newEmail,
                'phone' => $patient->phone,
            ];

            $response = $this->actingAs($this->admin)
                ->putJson(route('api.v1.admin.patients.update', $patient), $updatePayload);

            expect($response->status())->toBe(200);
            expect($response->json('data.email'))->toBe($newEmail);
        });

        it('prevents email conflict when updating', function () {
            $patient1 = $this->createPatient();
            $patient2 = $this->createPatient();

            // Try to update patient2 with patient1's email
            $updatePayload = [
                'email' => $patient1->email,
                'phone' => $patient2->phone,
            ];

            $response = $this->actingAs($this->admin)
                ->putJson(route('api.v1.admin.patients.update', $patient2), $updatePayload);

            expect($response->status())->toBe(422);
        });

        it('prevents phone conflict when updating', function () {
            $patient1 = $this->createPatient();
            $patient2 = $this->createPatient();

            // Try to update patient2 with patient1's phone
            $updatePayload = [
                'phone' => $patient1->phone,
                'email' => $patient2->email,
            ];

            $response = $this->actingAs($this->admin)
                ->putJson(route('api.v1.admin.patients.update', $patient2), $updatePayload);

            expect($response->status())->toBe(422);
        });

        it('accepts same email when patient updates own record', function () {
            $patient = $this->createPatient();
            $originalEmail = $patient->email;

            $updatePayload = [
                'email' => $originalEmail,
                'first_name' => 'Updated',
                'phone' => $patient->phone,
            ];

            $response = $this->actingAs($this->admin)
                ->putJson(route('api.v1.admin.patients.update', $patient), $updatePayload);

            expect($response->status())->toBe(200);
            expect($response->json('data.email'))->toBe($originalEmail);
        });

        it('accepts same phone when patient updates own record', function () {
            $patient = $this->createPatient();
            $originalPhone = $patient->phone;

            $updatePayload = [
                'phone' => $originalPhone,
                'first_name' => 'Updated',
                'email' => $patient->email,
            ];

            $response = $this->actingAs($this->admin)
                ->putJson(route('api.v1.admin.patients.update', $patient), $updatePayload);

            expect($response->status())->toBe(200);
            expect($response->json('data.phone'))->toBe($originalPhone);
        });

        it('properly converts date_of_birth DateTime to string', function () {
            $patient = $this->createPatient();
            $newDate = '1995-07-20';

            $updatePayload = [
                'date_of_birth' => $newDate,
                'email' => $patient->email,
                'phone' => $patient->phone,
            ];

            $response = $this->actingAs($this->admin)
                ->putJson(route('api.v1.admin.patients.update', $patient), $updatePayload);

            expect($response->status())->toBe(200);
            expect($response->json('data.date_of_birth'))->toBe($newDate);

            $patient->refresh();
            expect($patient->date_of_birth->toDateString())->toBe($newDate);
        });

        it('updates full_name when first or last name changes', function () {
            $patient = $this->createPatient();

            $updatePayload = [
                'first_name' => 'NewFirst',
                'last_name' => 'NewLast',
                'email' => $patient->email,
                'phone' => $patient->phone,
            ];

            $response = $this->actingAs($this->admin)
                ->putJson(route('api.v1.admin.patients.update', $patient), $updatePayload);

            expect($response->status())->toBe(200);
            expect($response->json('data.full_name'))->toContain('NewFirst');
            expect($response->json('data.full_name'))->toContain('NewLast');
        });
    });

    describe('5. Delete Patient (DELETE)', function () {
        it('deletes a patient', function () {
            $patient = $this->createPatient();
            $patientId = $patient->id;

            $response = $this->actingAs($this->admin)
                ->deleteJson(route('api.v1.admin.patients.destroy', $patient));

            expect($response->status())->toBe(200);
            expect(Patient::find($patientId))->toBeNull();
        });

        it('returns 404 when deleting non-existent patient', function () {
            $response = $this->actingAs($this->admin)
                ->deleteJson(route('api.v1.admin.patients.destroy', 99999));

            expect($response->status())->toBe(404);
        });
    });

    describe('6. Medical History', function () {
        it('updates patient medical history', function () {
            $patient = $this->createPatient();

            $historyPayload = [
                'allergies' => 'Penicillin, Sulfa',
                'chronic_diseases' => 'Diabetes',
                'current_medications' => 'Metformin',
                'medical_notes' => 'Patient has diabetes',
                'important_alerts' => 'Severe penicillin allergy',
            ];

            $response = $this->actingAs($this->admin)
                ->putJson(route('api.v1.admin.patients.updateMedicalHistory', $patient), $historyPayload);

            expect($response->status())->toBe(200);
            expect($response->json('allergies'))->toBe('Penicillin, Sulfa');
            expect($response->json('chronic_diseases'))->toBe('Diabetes');
        });

        it('handles partial medical history updates', function () {
            $patient = $this->createPatient();

            $historyPayload = [
                'allergies' => 'Penicillin',
            ];

            $response = $this->actingAs($this->admin)
                ->putJson(route('api.v1.admin.patients.updateMedicalHistory', $patient), $historyPayload);

            expect($response->status())->toBe(200);
        });
    });

    describe('7. Emergency Contacts', function () {
        it('adds an emergency contact', function () {
            $patient = $this->createPatient();

            $contactPayload = [
                'name' => 'John Emergency',
                'relation' => 'Spouse',
                'phone' => '01234567890',
                'notes' => 'Primary emergency contact',
            ];

            $response = $this->actingAs($this->admin)
                ->postJson(route('api.v1.admin.patients.addEmergencyContact', $patient), $contactPayload);

            expect($response->status())->toBe(201);
            expect($response->json('data.name'))->toBe('John Emergency');
            expect($response->json('data.relation'))->toBe('Spouse');
        });

        it('lists emergency contacts for patient', function () {
            $patient = $this->createPatient();

            // Add some contacts
            for ($i = 0; $i < 3; $i++) {
                $contactPayload = [
                    'name' => "Contact {$i}",
                    'relation' => 'Family',
                    'phone' => '01' . random_int(100000000, 999999999),
                ];

                $this->actingAs($this->admin)
                    ->postJson(route('api.v1.admin.patients.addEmergencyContact', $patient), $contactPayload);
            }

            $response = $this->actingAs($this->admin)
                ->getJson(route('api.v1.admin.patients.getEmergencyContacts', $patient));

            expect($response->status())->toBe(200);
            expect($response->json('data'))->toBeArray();
            expect(count($response->json('data')))->toBe(3);
        });
    });

    describe('8. Related Modules Integration', function () {
        it('patient can have appointments', function () {
            $patient = $this->createPatient();

            expect($patient->appointments()->count())->toBe(0);
        });

        it('patient can have visits', function () {
            $patient = $this->createPatient();

            expect($patient->visits()->count())->toBe(0);
        });

        it('patient medical records are loadable', function () {
            $patient = $this->createPatient();
            $patient->load('medicalHistory', 'emergencyContacts');

            expect($patient->medicalHistory)->not->toBeNull();
        });
    });
});

