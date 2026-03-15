<?php

namespace App\Modules\Patients\Services;

use App\Models\Patient\EmergencyContact;
use App\Models\Patient\Patient;
use Illuminate\Pagination\Paginator;

class PatientService
{
    /**
     * Get paginated list of patients with filters.
     */
    public function getPatientsWithFilters(
        int $page = 1,
        int $perPage = 15,
        ?string $search = null,
        ?string $status = null,
        ?string $sortBy = 'created_at',
        ?string $sortDirection = 'desc'
    ): Paginator {
        $query = Patient::query();

        // Search by name, email, phone, or patient code
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('patient_code', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($status) {
            $query->where('status', $status);
        }

        // Sort
        if (in_array($sortBy, ['created_at', 'updated_at', 'first_name', 'last_name', 'phone'])) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->latest();
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get patient with all relationships.
     */
    public function getPatientWithRelations(Patient $patient): Patient
    {
        return $patient->load([
            'profile',
            'medicalHistory',
            'emergencyContacts',
        ]);
    }

    /**
     * Get medical summary for patient.
     */
    public function getMedicalSummary(Patient $patient): Patient
    {
        return $patient->load([
            'profile',
            'medicalHistory',
            'emergencyContacts',
            'visits' => fn($q) => $q->latest()->limit(5),
        ]);
    }

    /**
     * Get emergency contacts for patient.
     */
    public function getEmergencyContacts(Patient $patient): mixed
    {
        return $patient->emergencyContacts()->get();
    }

    /**
     * Delete emergency contact.
     */
    public function deleteEmergencyContact(EmergencyContact $contact): bool
    {
        return $contact->delete();
    }
}

