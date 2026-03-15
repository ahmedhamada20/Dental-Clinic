<?php

namespace App\Support;

use Illuminate\Support\Str;

class CodeGenerator
{
    /**
     * Generate unique code with prefix
     */
    public static function generate(string $prefix = '', int $length = 8): string
    {
        $code = Str::random($length);

        if ($prefix) {
            $code = strtoupper($prefix) . '-' . $code;
        }

        return $code;
    }

    /**
     * Generate appointment code: APT-YYYYMMDD-XXXX
     */
    public static function appointmentCode(): string
    {
        $date = date('Ymd');
        $random = strtoupper(Str::random(4));

        return "APT-{$date}-{$random}";
    }

    /**
     * Generate invoice code: INV-YYYYMMDD-XXXX
     */
    public static function invoiceCode(): string
    {
        $date = date('Ymd');
        $random = strtoupper(Str::random(4));

        return "INV-{$date}-{$random}";
    }

    /**
     * Generate patient code: PAT-XXXXXX
     */
    public static function patientCode(): string
    {
        return 'PAT-' . strtoupper(Str::random(6));
    }

    /**
     * Generate receipt code: RCP-YYYYMMDD-XXXX
     */
    public static function receiptCode(): string
    {
        $date = date('Ymd');
        $random = strtoupper(Str::random(4));

        return "RCP-{$date}-{$random}";
    }

    /**
     * Generate visit code: VIS-YYYYMMDD-XXXX
     */
    public static function visitCode(): string
    {
        $date = date('Ymd');
        $random = strtoupper(Str::random(4));

        return "VIS-{$date}-{$random}";
    }

    /**
     * Generate treatment plan code: TRE-XXXXXX
     */
    public static function treatmentPlanCode(): string
    {
        return 'TRE-' . strtoupper(Str::random(6));
    }

    /**
     * Generate unique numeric code
     */
    public static function numericCode(int $length = 10): string
    {
        return str_pad(random_int(0, 10 ** $length - 1), $length, '0', STR_PAD_LEFT);
    }

    /**
     * Generate ticket code: TKT-YYYYMMDD-XXXX
     */
    public static function ticketCode(): string
    {
        $date = date('Ymd');
        $random = strtoupper(Str::random(4));

        return "TKT-{$date}-{$random}";
    }
}

