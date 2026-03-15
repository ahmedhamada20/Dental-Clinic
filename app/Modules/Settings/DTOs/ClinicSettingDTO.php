<?php

namespace App\Modules\Settings\DTOs;

final class ClinicSettingDTO
{
    public function __construct(
        public readonly string $clinicName,
        public readonly ?string $phone,
        public readonly ?string $email,
        public readonly ?string $address,
        public readonly ?string $timezone
    ) {
    }

    public static function fromArray(array $validated): self
    {
        return new self(
            clinicName: $validated['clinic_name'],
            phone: $validated['phone'] ?? null,
            email: $validated['email'] ?? null,
            address: $validated['address'] ?? null,
            timezone: $validated['timezone'] ?? null,
        );
    }
}
