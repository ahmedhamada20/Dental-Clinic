<?php

namespace App\Modules\Auth\Actions;

use App\Enums\PatientStatus;
use App\Models\Patient\Patient;
use App\Models\System\DeviceToken;
use App\Modules\Auth\DTOs\PatientRegisterData;
use App\Modules\Auth\Services\TokenAuthService;
use Illuminate\Support\Facades\Hash;

class PatientRegisterAction
{
    public function __construct(
        private readonly TokenAuthService $tokenService
    ) {
    }

    /**
     * Execute the patient registration action.
     *
     * @param PatientRegisterData $data
     * @return array{patient: Patient, token: string, firebase_token_registered: bool}
     */
    public function execute(PatientRegisterData $data): array
    {
        // Create the patient account
        $patient = Patient::create([
            'first_name' => $data->first_name,
            'last_name' => $data->last_name,
            'full_name' => trim($data->first_name . ' ' . $data->last_name),
            'phone' => $data->phone,
            'email' => $data->email,
            'password' => Hash::make($data->password),
            'gender' => $data->gender,
            'status' => PatientStatus::ACTIVE,
            'registered_from' => 'mobile_app',
        ]);

        // Create authentication token
        $accessToken = $this->tokenService->createToken(
            $patient,
            $data->device_name
        );

        $firebaseTokenRegistered = $this->registerFirebaseToken($patient, $data);

        return [
            'patient' => $patient->fresh(),
            'token' => $this->tokenService->getPlainTextToken($accessToken),
            'firebase_token_registered' => $firebaseTokenRegistered,
        ];
    }

    private function registerFirebaseToken(Patient $patient, PatientRegisterData $data): bool
    {
        if (blank($data->firebase_token)) {
            return false;
        }

        DeviceToken::query()->updateOrCreate(
            [
                'patient_id' => $patient->id,
                'firebase_token' => $data->firebase_token,
            ],
            [
                'device_type' => $data->device_type,
                'device_name' => $data->device_name,
                'is_active' => true,
                'last_used_at' => now(),
            ]
        );

        return true;
    }
}

