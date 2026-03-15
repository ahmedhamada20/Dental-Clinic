<?php

namespace App\Modules\Patients\DTOs;

class UpdatePatientDTO
{
    public function __construct(
        public ?string $first_name = null,
        public ?string $last_name = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $alternate_phone = null,
        public ?string $gender = null,
        public ?\DateTime $date_of_birth = null,
        public ?string $address = null,
        public ?string $city = null,
        public ?string $profile_image = null,
        public ?string $status = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            first_name: $data['first_name'] ?? null,
            last_name: $data['last_name'] ?? null,
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            alternate_phone: $data['alternate_phone'] ?? null,
            gender: $data['gender'] ?? null,
            date_of_birth: isset($data['date_of_birth']) ? new \DateTime($data['date_of_birth']) : null,
            address: $data['address'] ?? null,
            city: $data['city'] ?? null,
            profile_image: $data['profile_image'] ?? null,
            status: $data['status'] ?? null,
        );
    }

    public function toArray(): array
    {
        $data = [];

        foreach ($this as $key => $value) {
            if ($value !== null) {
                // Convert DateTime objects to date string format
                if ($value instanceof \DateTime) {
                    $data[$key] = $value->format('Y-m-d');
                } else {
                    $data[$key] = $value;
                }
            }
        }

        if ($this->first_name !== null || $this->last_name !== null) {
            $fn = $this->first_name ?? '';
            $ln = $this->last_name ?? '';
            $data['full_name'] = trim("$fn $ln");
        }

        return $data;
    }
}

