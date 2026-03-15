<?php

namespace App\Modules\Auth\Controllers;

use App\Models\Patient\Patient;
use App\Modules\Patients\Actions\UpdatePatientAction;
use App\Modules\Patients\DTOs\UpdatePatientDTO;
use App\Modules\Patients\Requests\UpdatePatientProfileRequest;
use App\Modules\Patients\Resources\MedicalSummaryResource;
use App\Modules\Patients\Resources\PatientDetailsResource;
use App\Modules\Patients\Services\PatientService;
use App\Support\ApiResponse;
use Illuminate\Routing\Controller;

class PatientProfileController extends Controller
{
    /**
     * Get authenticated patient's profile.
     * GET /api/v1/patient/profile
     */
    public function show(): mixed
    {
        try {
            /** @var Patient $patient */
            $patient = auth('sanctum')->user();

            if (!$patient instanceof Patient) {
                return ApiResponse::error('Patient not found', 404);
            }

            $patient = app(PatientService::class)->getPatientWithRelations($patient);

            return ApiResponse::success(
                new PatientDetailsResource($patient),
                'Profile retrieved successfully'
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to retrieve profile: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Update authenticated patient's profile.
     * PUT /api/v1/patient/profile
     */
    public function update(UpdatePatientProfileRequest $request): mixed
    {
        try {
            /** @var Patient $patient */
            $patient = auth('sanctum')->user();

            if (!$patient instanceof Patient) {
                return ApiResponse::error('Patient not found', 404);
            }

            $dto = UpdatePatientDTO::fromArray($request->validated());
            $patient = app(UpdatePatientAction::class)($patient, $dto);

            // Update profile data if provided
            $profileData = $request->only([
                'occupation',
                'marital_status',
                'blood_group',
            ]);

            if (!empty($profileData)) {
                if ($patient->profile) {
                    $patient->profile->update($profileData);
                } else {
                    $profileData['patient_id'] = $patient->id;
                    $patient->profile()->create($profileData);
                }
            }

            $patient = $patient->load('profile', 'medicalHistory');

            return ApiResponse::success(
                new PatientDetailsResource($patient),
                'Profile updated successfully'
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to update profile: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Get patient's medical summary.
     * GET /api/v1/patient/profile/medical-summary
     */
    public function medicalSummary(): mixed
    {
        try {
            /** @var Patient $patient */
            $patient = auth('sanctum')->user();

            if (!$patient instanceof Patient) {
                return ApiResponse::error('Patient not found', 404);
            }

            $patient = app(PatientService::class)->getMedicalSummary($patient);

            return ApiResponse::success(
                new MedicalSummaryResource($patient),
                'Medical summary retrieved successfully'
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to retrieve medical summary: ' . $e->getMessage(),
                500
            );
        }
    }
}

