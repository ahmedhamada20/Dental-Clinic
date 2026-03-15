<?php

namespace App\Modules\Patients\DTOs;

class StorePatientDTO
{
    public function __construct(
        public string $first_name,
        public string $last_name,
        public string $email,
        public string $phone,
        public ?string $alternate_phone = null,
        public string $gender = 'other',
        public ?\DateTime $date_of_birth = null,
        public string $address = '',
        public string $city = '',
        public ?string $profile_image = null,
        public string $password = '',
        public string $status = 'active',
        public ?string $registered_from = 'mobile_app',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            first_name: $data['first_name'],
            last_name: $data['last_name'],
            email: $data['email'],
            phone: $data['phone'],
            alternate_phone: $data['alternate_phone'] ?? null,
            gender: $data['gender'] ?? 'other',
            date_of_birth: isset($data['date_of_birth']) ? new \DateTime($data['date_of_birth']) : null,
            address: $data['address'] ?? '',
            city: $data['city'] ?? '',
            profile_image: $data['profile_image'] ?? null,
            password: $data['password'] ?? '',
            status: $data['status'] ?? 'active',
            registered_from: $data['registered_from'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => trim("{$this->first_name} {$this->last_name}"),
            'email' => $this->email,
            'phone' => $this->phone,
            'alternate_phone' => $this->alternate_phone,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'address' => $this->address,
            'city' => $this->city,
            'profile_image' => $this->profile_image,
            'password' => $this->password,
            'status' => $this->status,
            'registered_from' => $this->registered_from,
        ];
    }
}

