<?php

namespace App\Modules\Patients\Controllers;

use App\Models\Patient\Patient;
use App\Modules\Patients\Actions\CreateEmergencyContactAction;
use App\Modules\Patients\Actions\CreatePatientAction;
use App\Modules\Patients\Actions\UpdatePatientAction;
use App\Modules\Patients\Actions\UpdatePatientMedicalHistoryAction;
use App\Modules\Patients\DTOs\EmergencyContactDTO;
use App\Modules\Patients\DTOs\StorePatientDTO;
use App\Modules\Patients\DTOs\UpdateMedicalHistoryDTO;
use App\Modules\Patients\DTOs\UpdatePatientDTO;
use App\Modules\Patients\Requests\StoreEmergencyContactRequest;
use App\Modules\Patients\Requests\StorePatientRequest;
use App\Modules\Patients\Requests\UpdatePatientMedicalHistoryRequest;
use App\Modules\Patients\Requests\UpdatePatientRequest;
use App\Modules\Patients\Resources\EmergencyContactResource;
use App\Modules\Patients\Resources\PatientDetailsResource;
use App\Modules\Patients\Resources\PatientResource;
use App\Support\ApiResponse;
use Illuminate\Routing\Controller;

class PatientController extends Controller
{

    /**
     * List all patients with pagination and filters.
     * GET /api/v1/admin/patients
     */
    public function index(): mixed
    {
        $search = request()->query('search');
        $status = request()->query('status');
        $perPage = request()->query('per_page', 15);
        $sortBy = request()->query('sort_by', 'created_at');
        $sortDirection = request()->query('sort_direction', 'desc');

        $patients = app(PatientService::class)->getPatientsWithFilters(
            page: request()->query('page', 1),
            perPage: min((int)$perPage, 100),
            search: $search,
            status: $status,
            sortBy: $sortBy,
            sortDirection: $sortDirection,
        );

        return ApiResponse::paginated(
            $patients,
            'Patients retrieved successfully',
            200
        );
    }

    /**
     * Create a new patient.
     * POST /api/v1/admin/patients
     */
    public function store(StorePatientRequest $request): mixed
    {
        try {
            $dto = StorePatientDTO::fromArray($request->validated());
            $patient = app(CreatePatientAction::class)($dto);

            return ApiResponse::success(
                new PatientDetailsResource($patient),
                'Patient created successfully',
                201
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to create patient: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Get patient details.
     * GET /api/v1/admin/patients/{id}
     */
    public function show(Patient $patient): mixed
    {
        $patient = app(PatientService::class)->getPatientWithRelations($patient);

        return ApiResponse::success(
            new PatientDetailsResource($patient),
            'Patient retrieved successfully'
        );
    }

    /**
     * Update patient information.
     * PUT /api/v1/admin/patients/{id}
     */
    public function update(UpdatePatientRequest $request, Patient $patient): mixed
    {
        try {
            $dto = UpdatePatientDTO::fromArray($request->validated());
            $patient = app(UpdatePatientAction::class)($patient, $dto);

            return ApiResponse::success(
                new PatientDetailsResource($patient),
                'Patient updated successfully'
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to update patient: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Update patient medical history.
     * PUT /api/v1/admin/patients/{id}/medical-history
     */
    public function updateMedicalHistory(UpdatePatientMedicalHistoryRequest $request, Patient $patient): mixed
    {
        try {
            $dto = UpdateMedicalHistoryDTO::fromArray($request->validated());
            app(UpdatePatientMedicalHistoryAction::class)($patient, $dto);

            $patient = $patient->load('medicalHistory');

            return ApiResponse::success(
                $patient->medicalHistory,
                'Medical history updated successfully'
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to update medical history: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Add emergency contact.
     * POST /api/v1/admin/patients/{id}/emergency-contacts
     */
    public function addEmergencyContact(StoreEmergencyContactRequest $request, Patient $patient): mixed
    {
        try {
            $dto = EmergencyContactDTO::fromArray($request->validated());
            $contact = app(CreateEmergencyContactAction::class)($patient, $dto);

            return ApiResponse::success(
                new EmergencyContactResource($contact),
                'Emergency contact added successfully',
                201
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to add emergency contact: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Get patient emergency contacts.
     * GET /api/v1/admin/patients/{id}/emergency-contacts
     */
    public function getEmergencyContacts(Patient $patient): mixed
    {
        $contacts = app(PatientService::class)->getEmergencyContacts($patient);

        return ApiResponse::success(
            EmergencyContactResource::collection($contacts),
            'Emergency contacts retrieved successfully'
        );
    }

    /**
     * Delete patient.
     * DELETE /api/v1/admin/patients/{id}
     */
    public function destroy(Patient $patient): mixed
    {
        try {
            $patient->delete();

            return ApiResponse::success(
                null,
                'Patient deleted successfully'
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to delete patient: ' . $e->getMessage(),
                500
            );
        }
    }
}

