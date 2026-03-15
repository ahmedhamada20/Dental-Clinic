<?php

namespace App\Modules\Notifications\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Email notification channel service.
 * Sends email directly via Laravel Mail to a recipient address.
 */
class EmailNotificationService
{
    /**
     * Send a notification email to a given address.
     *
     * @param  string  $toEmail
     * @param  string  $toName
     * @param  string  $subject
     * @param  string  $body
     * @param  array   $data   Extra context passed to the mail view
     * @return bool
     */
    public function send(string $toEmail, string $toName, string $subject, string $body, array $data = []): bool
    {
        try {
            Mail::send(
                [],
                [],
                function ($message) use ($toEmail, $toName, $subject, $body) {
                    $message->to($toEmail, $toName)
                        ->subject($subject)
                        ->html($this->buildHtml($subject, $body));
                }
            );

            return true;
        } catch (\Throwable $e) {
            Log::error('EmailNotificationService: failed to send email', [
                'to'      => $toEmail,
                'subject' => $subject,
                'error'   => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function buildHtml(string $subject, string $body): string
    {
        $escapedSubject = e($subject);
        $escapedBody    = nl2br(e($body));

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    body { font-family: Arial, sans-serif; background:#f6f6f6; padding:20px; }
    .card { background:#fff; border-radius:8px; padding:30px; max-width:600px; margin:0 auto; }
    h2 { color:#4f46e5; }
    p { color:#374151; line-height:1.6; }
    .footer { margin-top:20px; font-size:12px; color:#9ca3af; }
  </style>
</head>
<body>
  <div class="card">
    <h2>{$escapedSubject}</h2>
    <p>{$escapedBody}</p>
    <div class="footer">Dental Clinic System &mdash; automated notification</div>
  </div>
</body>
</html>
HTML;
    }
}

