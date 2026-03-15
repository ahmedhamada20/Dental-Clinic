<?php

namespace App\Modules\Auth\DTOs;

use App\Modules\Auth\Requests\PatientRegisterRequest;

class PatientRegisterData
{
    public function __construct(
        public readonly string $first_name,
        public readonly string $last_name,
        public readonly string $phone,
        public readonly ?string $email,
        public readonly string $password,
        public readonly ?string $gender,
        public readonly ?string $device_name,
    ) {
    }

    public static function fromRequest(PatientRegisterRequest $request): self
    {
        return new self(
            first_name: $request->validated('first_name'),
            last_name: $request->validated('last_name'),
            phone: $request->validated('phone'),
            email: $request->validated('email'),
            password: $request->validated('password'),
            gender: $request->validated('gender'),
            device_name: $request->validated('device_name', 'mobile'),
        );
    }
}

