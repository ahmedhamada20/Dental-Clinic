<?php

namespace Database\Seeders;


use App\Models\Patient\EmergencyContact;
use App\Models\Patient\Patient;
use App\Models\Patient\PatientMedicalHistory;
use App\Models\Patient\PatientProfile;
use App\Models\System\DeviceToken;
use Illuminate\Database\Seeder;

class Phase3PatientsSeeder extends Seeder
{
    public function run(): void
    {
        // Create a diverse base set for dashboard and API testing.
        $patients = Patient::factory()
            ->count(60)
            ->create();

        $patients->each(function (Patient $patient): void {
            // One-to-one profile (safe against duplicates).
            PatientProfile::query()->updateOrCreate(
                ['patient_id' => $patient->id],
                PatientProfile::factory()->make(['patient_id' => $patient->id])->toArray()
            );

            // One-to-one medical history (safe against duplicates).
            PatientMedicalHistory::query()->updateOrCreate(
                ['patient_id' => $patient->id],
                PatientMedicalHistory::factory()->make(['patient_id' => $patient->id])->toArray()
            );

            // One-to-many emergency contacts (0..2).
            $contactsCount = fake()->numberBetween(0, 2);
            for ($i = 0; $i < $contactsCount; $i++) {
                EmergencyContact::factory()->create(['patient_id' => $patient->id]);
            }

            // Optional device tokens (0..2), more likely for mobile-app registrations.
            $maxTokens = $patient->registered_from === 'mobile_app' ? 2 : 1;
            $tokensCount = fake()->numberBetween(0, $maxTokens);
            for ($i = 0; $i < $tokensCount; $i++) {
                DeviceToken::factory()->create(['patient_id' => $patient->id]);
            }
        });
    }
}
