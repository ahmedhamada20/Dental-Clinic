<?php

namespace App\Modules\Patients\Actions;

use App\Models\Patient\Patient;
use App\Modules\Patients\DTOs\StorePatientDTO;
use Illuminate\Support\Facades\Hash;

class CreatePatientAction
{
    public function __invoke(StorePatientDTO $dto): Patient
    {
        $data = $dto->toArray();

        // Generate patient code
        $data['patient_code'] = $this->generatePatientCode();

        // Hash password if provided
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // Remove null values
        $data = array_filter($data, fn($value) => $value !== null);

        return Patient::create($data);
    }

    private function generatePatientCode(): string
    {
        $timestamp = now()->format('YmdHis');
        $random = random_int(1000, 9999);
        return "PAT-{$timestamp}-{$random}";
    }
}

