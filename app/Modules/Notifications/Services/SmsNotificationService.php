<?php

namespace App\Modules\Notifications\Services;

use Illuminate\Support\Facades\Log;

/**
 * SMS notification channel service.
 * Currently a stub — replace the dispatch() body with a real SMS provider
 * (e.g. Vonage, Twilio, or a local gateway) when credentials are available.
 */
class SmsNotificationService
{
    /**
     * Send an SMS to the given phone number.
     *
     * @param  string  $phone    E.164 or local format accepted by your provider
     * @param  string  $message
     * @return bool
     */
    public function send(string $phone, string $message): bool
    {
        try {
            // -----------------------------------------------------------------
            // TODO: Replace the log stub below with a real SMS provider call.
            //
            // Example (Vonage):
            //   $vonage = app(\Vonage\Client::class);
            //   $vonage->sms()->send(
            //       new \Vonage\SMS\Message\SMS($phone, config('services.sms.from'), $message)
            //   );
            //
            // Example (Twilio):
            //   $twilio = new \Twilio\Rest\Client(config('services.twilio.sid'), config('services.twilio.token'));
            //   $twilio->messages->create($phone, [
            //       'from' => config('services.twilio.from'),
            //       'body' => $message,
            //   ]);
            // -----------------------------------------------------------------

            Log::info('SmsNotificationService: dispatch (stub)', [
                'to'      => $phone,
                'message' => $message,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('SmsNotificationService: failed to send SMS', [
                'to'    => $phone,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}

