<?php

namespace App\Modules\Notifications\DTOs;

final class RegisterDeviceTokenDTO
{
    public function __construct(
        public readonly string $deviceType,
        public readonly string $firebaseToken,
        public readonly ?string $deviceName,
        public readonly ?string $appVersion
    ) {
    }

    public static function fromArray(array $validated): self
    {
        return new self(
            deviceType: $validated['device_type'],
            firebaseToken: $validated['firebase_token'],
            deviceName: $validated['device_name'] ?? null,
            appVersion: $validated['app_version'] ?? null,
        );
    }
}
