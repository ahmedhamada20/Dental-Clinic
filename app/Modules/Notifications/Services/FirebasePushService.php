<?php

namespace App\Modules\Notifications\Services;

use App\Models\System\DeviceToken;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class FirebasePushService
{
    public function sendToPatient(int $patientId, string $title, string $body, array $data = []): void
    {
        $tokens = DeviceToken::query()
            ->where('patient_id', $patientId)
            ->where('is_active', true)
            ->pluck('firebase_token');

        $this->sendToTokens($tokens, $title, $body, $data);
    }

    public function sendToTokens(Collection $tokens, string $title, string $body, array $data = []): void
    {
        // Placeholder integration point for real Firebase SDK/HTTP implementation.
        Log::info('Firebase push dispatch', [
            'tokens_count' => $tokens->count(),
            'title' => $title,
            'body' => $body,
            'data' => $data,
        ]);
    }
}
