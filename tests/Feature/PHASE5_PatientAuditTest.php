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

describe('Patient Index', function () {
    it('can list patients', function () {
        $this->actingAs($this->admin)
            ->getJson(route('api.admin.patients.index'))
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'data',
                    'pagination'
                ]
            ]);
    });
});

describe('Patient Create/Store', function () {
    it('can create a patient via API', function () {
        $payload = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe' . time() . '@example.com',
            'phone' => '01' . random_int(100000000, 999999999),
            'gender' => 'male',
            'date_of_birth' => now()->subYears(30)->toDateString(),
            'address' => 'Test Address',
            'city' => 'Cairo',
            'status' => 'active',
        ];

        $response = $this->actingAs($this->admin)
            ->postJson(route('api.admin.patients.store'), $payload);

        echo "\n\nCreate Patient Response:\n";
        echo $response->getContent() . "\n";

        $response->assertCreated()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'patient_code',
                    'first_name',
                    'last_name',
                ]
            ]);

        $patientId = $response->json('data.id');
        $this->assertIsNotNull($patientId);
    });
});

describe('Patient Show', function () {
    it('can show a patient', function () {
        $patient = $this->createPatient();

        $response = $this->actingAs($this->admin)
            ->getJson(route('api.admin.patients.show', $patient));

        echo "\n\nShow Patient Response:\n";
        echo $response->getContent() . "\n";

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'patient_code',
                    'first_name',
                    'last_name',
                ]
            ]);
    });
});

describe('Patient Update', function () {
    it('can update a patient', function () {
        $patient = $this->createPatient([
            'first_name' => 'Original',
        ]);

        $updatePayload = [
            'first_name' => 'Updated Name',
            'last_name' => 'New Last',
            'email' => $patient->email,
            'phone' => $patient->phone,
            'gender' => 'female',
            'date_of_birth' => now()->subYears(25)->toDateString(),
            'address' => 'Updated Address',
            'city' => 'Giza',
            'status' => 'active',
        ];

        $response = $this->actingAs($this->admin)
            ->putJson(route('api.admin.patients.update', $patient), $updatePayload);

        echo "\n\nUpdate Patient Response:\n";
        echo $response->getContent() . "\n";

        if (!$response->isOk()) {
            echo "\nError Details:\n";
            if ($response->json('errors')) {
                echo json_encode($response->json('errors'), JSON_PRETTY_PRINT) . "\n";
            }
        }

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'first_name',
                ]
            ]);

        $patient->refresh();
        expect($patient->first_name)->toBe('Updated Name');
    });

    it('validates unique email on update', function () {
        $patient1 = $this->createPatient(['email' => 'patient1@example.com']);
        $patient2 = $this->createPatient(['email' => 'patient2@example.com']);

        $updatePayload = [
            'email' => 'patient1@example.com', // Try to use patient1's email
        ];

        $response = $this->actingAs($this->admin)
            ->putJson(route('api.admin.patients.update', $patient2), $updatePayload);

        echo "\n\nUnique Validation Response:\n";
        echo $response->getContent() . "\n";

        $response->assertUnprocessable();
    });
});

describe('Patient Delete', function () {
    it('can delete a patient', function () {
        $patient = $this->createPatient();

        $response = $this->actingAs($this->admin)
            ->deleteJson(route('api.admin.patients.destroy', $patient));

        echo "\n\nDelete Patient Response:\n";
        echo $response->getContent() . "\n";

        $response->assertOk();
        expect(Patient::find($patient->id))->toBeNull();
    });
});

describe('Patient Medical History', function () {
    it('can update medical history', function () {
        $patient = $this->createPatient();

        $payload = [
            'allergies' => 'Penicillin',
            'current_medications' => 'Aspirin',
            'medical_conditions' => 'Diabetes',
        ];

        $response = $this->actingAs($this->admin)
            ->putJson(route('api.admin.patients.updateMedicalHistory', $patient), $payload);

        echo "\n\nUpdate Medical History Response:\n";
        echo $response->getContent() . "\n";

        $response->assertOk();
    });
});

describe('Patient Emergency Contacts', function () {
    it('can add emergency contact', function () {
        $patient = $this->createPatient();

        $payload = [
            'name' => 'Emergency Contact',
            'relationship' => 'spouse',
            'phone' => '01234567890',
            'email' => 'emergency@example.com',
        ];

        $response = $this->actingAs($this->admin)
            ->postJson(route('api.admin.patients.addEmergencyContact', $patient), $payload);

        echo "\n\nAdd Emergency Contact Response:\n";
        echo $response->getContent() . "\n";

        $response->assertCreated();
    });

    it('can get emergency contacts', function () {
        $patient = $this->createPatient();

        $response = $this->actingAs($this->admin)
            ->getJson(route('api.admin.patients.getEmergencyContacts', $patient));

        echo "\n\nGet Emergency Contacts Response:\n";
        echo $response->getContent() . "\n";

        $response->assertOk();
    });
});

