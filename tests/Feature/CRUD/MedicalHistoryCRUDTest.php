<?php

use App\Models\Patient\Patient;
use App\Models\Patient\PatientMedicalHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->patient = Patient::factory()->create();
});

describe('Medical History CRUD Operations', function () {

    describe('Store', function () {
        it('creates medical history for patient with valid data', function () {
            $data = [
                'allergies' => 'Penicillin, Latex',
                'chronic_diseases' => 'Diabetes, Hypertension',
                'current_medications' => 'Metformin 500mg',
                'medical_notes' => 'General medical notes',
                'dental_history' => 'Regular cleanings',
                'important_alerts' => 'Allergic reactions reported',
            ];

            $response = $this->post(
                route('admin.patients.medical-history.store', $this->patient),
                $data
            );

            $response->assertRedirect(route('admin.patients.show', [
                'patient' => $this->patient->id,
                'tab' => 'history'
            ]))
                ->assertSessionHas('success');

            $this->assertDatabaseHas('patient_medical_histories', [
                'patient_id' => $this->patient->id,
                'allergies' => 'Penicillin, Latex',
            ]);
        });

        it('accepts partial medical history data', function () {
            $data = [
                'allergies' => 'Penicillin',
                'chronic_diseases' => null,
                'current_medications' => null,
                'medical_notes' => null,
                'dental_history' => null,
                'important_alerts' => null,
            ];

            $response = $this->post(
                route('admin.patients.medical-history.store', $this->patient),
                $data
            );

            $response->assertSessionHas('success');

            $this->assertDatabaseHas('patient_medical_histories', [
                'patient_id' => $this->patient->id,
                'allergies' => 'Penicillin',
            ]);
        });

        it('accepts all null medical history fields', function () {
            $data = [
                'allergies' => null,
                'chronic_diseases' => null,
                'current_medications' => null,
                'medical_notes' => null,
                'dental_history' => null,
                'important_alerts' => null,
            ];

            $response = $this->post(
                route('admin.patients.medical-history.store', $this->patient),
                $data
            );

            $response->assertSessionHas('success');

            $this->assertDatabaseHas('patient_medical_histories', [
                'patient_id' => $this->patient->id,
            ]);
        });

        it('stores user id when updating medical history', function () {
            $data = [
                'allergies' => 'Penicillin',
                'chronic_diseases' => 'Diabetes',
                'current_medications' => null,
                'medical_notes' => null,
                'dental_history' => null,
                'important_alerts' => null,
            ];

            $response = $this->post(
                route('admin.patients.medical-history.store', $this->patient),
                $data
            );

            $this->assertDatabaseHas('patient_medical_histories', [
                'patient_id' => $this->patient->id,
                'updated_by' => $this->user->id,
            ]);
        });

        it('updates existing medical history instead of creating duplicate', function () {
            // Create initial medical history
            $this->patient->medicalHistory()->create([
                'allergies' => 'Original allergy',
                'chronic_diseases' => 'Original disease',
            ]);

            // Update with new data
            $response = $this->post(
                route('admin.patients.medical-history.store', $this->patient),
                [
                    'allergies' => 'Updated allergy',
                    'chronic_diseases' => 'Updated disease',
                    'current_medications' => null,
                    'medical_notes' => null,
                    'dental_history' => null,
                    'important_alerts' => null,
                ]
            );

            // Should still be only one record
            $this->assertDatabaseCount('patient_medical_histories', 1);

            $this->assertDatabaseHas('patient_medical_histories', [
                'patient_id' => $this->patient->id,
                'allergies' => 'Updated allergy',
                'chronic_diseases' => 'Updated disease',
            ]);
        });

        it('persists important alerts to database', function () {
            $data = [
                'allergies' => 'Penicillin',
                'chronic_diseases' => null,
                'current_medications' => null,
                'medical_notes' => null,
                'dental_history' => null,
                'important_alerts' => 'Critical allergy information',
            ];

            $response = $this->post(
                route('admin.patients.medical-history.store', $this->patient),
                $data
            );

            $this->assertDatabaseHas('patient_medical_histories', [
                'patient_id' => $this->patient->id,
                'important_alerts' => 'Critical allergy information',
            ]);
        });

        it('stores long text in medical history fields', function () {
            $longText = str_repeat('Lorem ipsum dolor sit amet. ', 50);

            $data = [
                'allergies' => $longText,
                'chronic_diseases' => $longText,
                'current_medications' => $longText,
                'medical_notes' => $longText,
                'dental_history' => $longText,
                'important_alerts' => $longText,
            ];

            $response = $this->post(
                route('admin.patients.medical-history.store', $this->patient),
                $data
            );

            $response->assertSessionHas('success');

            $history = PatientMedicalHistory::where('patient_id', $this->patient->id)->first();
            expect($history->allergies)->toBe($longText);
        });

        it('replaces all medical history fields when updated', function () {
            // Create initial medical history
            $this->patient->medicalHistory()->create([
                'allergies' => 'Penicillin',
                'chronic_diseases' => 'Diabetes',
                'current_medications' => 'Metformin',
                'medical_notes' => 'Initial notes',
                'dental_history' => 'Initial dental history',
                'important_alerts' => 'Initial alerts',
            ]);

            // Update with completely different data
            $this->post(
                route('admin.patients.medical-history.store', $this->patient),
                [
                    'allergies' => 'Aspirin',
                    'chronic_diseases' => 'Hypertension',
                    'current_medications' => 'Lisinopril',
                    'medical_notes' => 'Updated notes',
                    'dental_history' => 'Updated dental history',
                    'important_alerts' => 'Updated alerts',
                ]
            );

            $this->assertDatabaseHas('patient_medical_histories', [
                'patient_id' => $this->patient->id,
                'allergies' => 'Aspirin',
                'chronic_diseases' => 'Hypertension',
                'current_medications' => 'Lisinopril',
                'medical_notes' => 'Updated notes',
                'dental_history' => 'Updated dental history',
                'important_alerts' => 'Updated alerts',
            ]);
        });
    });
});

