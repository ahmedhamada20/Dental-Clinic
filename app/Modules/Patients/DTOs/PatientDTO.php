<?php

namespace App\Modules\Patients\DTOs;

/**
 * @deprecated Use StorePatientDTO or UpdatePatientDTO instead
 */
class PatientDTO
{
    public function __construct(
        public string $first_name,
        public string $last_name,
        public string $email,
        public string $phone,
        public ?string $address = null,
        public ?string $city = null,
        public ?string $state = null,
        public ?string $zip_code = null,
    ) {}
}

