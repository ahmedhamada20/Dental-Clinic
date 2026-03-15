<?php

namespace App\Modules\Audit\Support;

class AuditValueSanitizer
{
    private const MASKED_FIELDS = [
        'password',
        'remember_token',
        'token',
        'api_token',
        'access_token',
        'refresh_token',
        'secret',
        'reference_no',
        'card_number',
        'cvv',
    ];

    public static function sanitize(?array $values): ?array
    {
        if ($values === null) {
            return null;
        }

        $sanitized = [];

        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = self::sanitize($value);
                continue;
            }

            $sanitized[$key] = self::shouldMask((string) $key)
                ? '***'
                : $value;
        }

        return $sanitized;
    }

    private static function shouldMask(string $key): bool
    {
        $normalized = strtolower($key);

        foreach (self::MASKED_FIELDS as $field) {
            if (str_contains($normalized, $field)) {
                return true;
            }
        }

        return false;
    }
}

