<?php

namespace Database\Seeders\Phase1;

use App\Enums\NotificationType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * NotificationTypeSeeder
 *
 * Documents and validates notification types used by the system.
 * These are PHP enums but this seeder ensures all required types are available.
 *
 * Manages core notification types:
 * - Appointment-related (confirmed, reminder, cancelled, rescheduled)
 * - Medical-related (prescription ready, treatment plan approved)
 * - Payment-related (received, reminder)
 * - Invoice-related (generated)
 * - General notifications
 */
class NotificationTypeSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->validateNotificationTypes();
    }

    /**
     * Validate that NotificationType enum has all required cases.
     */
    private function validateNotificationTypes(): void
    {
        $requiredCases = [
            'APPOINTMENT_CONFIRMED',
            'APPOINTMENT_REMINDER',
            'APPOINTMENT_CANCELLED',
            'APPOINTMENT_RESCHEDULED',
            'PRESCRIPTION_READY',
            'TREATMENT_PLAN_APPROVED',
            'PAYMENT_RECEIVED',
            'PAYMENT_REMINDER',
            'INVOICE_GENERATED',
            'GENERAL',
        ];

        $enum = NotificationType::class;
        foreach ($requiredCases as $case) {
            if (!defined("$enum::$case")) {
                throw new \RuntimeException("NotificationType enum missing case: $case");
            }
        }

        // Validate methods exist
        $type = NotificationType::APPOINTMENT_CONFIRMED;
        if (!method_exists($type, 'label')) {
            throw new \RuntimeException('NotificationType enum missing required methods');
        }

        // Log available notification types for debugging
        $this->command->info('✓ All notification types validated successfully');
    }
}

