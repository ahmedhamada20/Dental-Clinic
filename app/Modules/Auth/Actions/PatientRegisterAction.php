<?php

namespace App\Modules\Auth\Actions;

use App\Enums\PatientStatus;
use App\Models\Patient\Patient;
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
     * @return array{patient: Patient, token: string}
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

        return [
            'patient' => $patient->fresh(),
            'token' => $this->tokenService->getPlainTextToken($accessToken),
        ];
    }
}

