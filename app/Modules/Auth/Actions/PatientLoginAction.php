<?php

namespace App\Modules\Auth\Actions;

use App\Models\Patient\Patient;
use App\Models\System\DeviceToken;
use App\Modules\Auth\DTOs\PatientLoginData;
use App\Modules\Auth\Services\TokenAuthService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PatientLoginAction
{
    public function __construct(
        private readonly TokenAuthService $tokenService
    ) {
    }

    /**
     * Execute the patient login action.
     *
     * @param PatientLoginData $data
     * @return array{patient: Patient, token: string, firebase_token_registered: bool}
     * @throws ValidationException
     */
    public function execute(PatientLoginData $data): array
    {
        // Find patient by phone
        $patient = Patient::where('phone', $data->phone)->first();

        // Verify credentials
        if (!$patient || !Hash::check($data->password, $patient->password)) {
            throw ValidationException::withMessages([
                'phone' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Update last login timestamp
        $patient->update([
            'last_login_at' => now(),
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

    private function registerFirebaseToken(Patient $patient, PatientLoginData $data): bool
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

