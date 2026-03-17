<?php

namespace App\Modules\Auth\DTOs;

use App\Modules\Auth\Requests\PatientLoginRequest;

class PatientLoginData
{
    public function __construct(
        public readonly string $phone,
        public readonly string $password,
        public readonly string $device_name,
        public readonly ?string $firebase_token,
        public readonly ?string $device_type,
    ) {
    }

    public static function fromRequest(PatientLoginRequest $request): self
    {
        return new self(
            phone: $request->validated('phone'),
            password: $request->validated('password'),
            device_name: $request->validated('device_name', 'mobile'),
            firebase_token: $request->validated('firebase_token'),
            device_type: $request->validated('device_type'),
        );
    }
}

