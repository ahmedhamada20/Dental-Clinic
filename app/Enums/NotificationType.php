<?php

namespace App\Enums;

enum NotificationType: string
{
    case APPOINTMENT_CONFIRMED = 'appointment_confirmed';
    case APPOINTMENT_REMINDER = 'appointment_reminder';
    case APPOINTMENT_CANCELLED = 'appointment_cancelled';
    case APPOINTMENT_RESCHEDULED = 'appointment_rescheduled';
    case PRESCRIPTION_READY = 'prescription_ready';
    case TREATMENT_PLAN_APPROVED = 'treatment_plan_approved';
    case PAYMENT_RECEIVED = 'payment_received';
    case PAYMENT_REMINDER = 'payment_reminder';
    case INVOICE_GENERATED = 'invoice_generated';
    case GENERAL = 'general';
    case WAITING_LIST_SLOT = 'waiting_list_slot';
    case BILLING_DUE = 'billing_due';
    case CUSTOM_ANNOUNCEMENT = 'custom_announcement';

    public function label(): string
    {
        return match ($this) {
            self::APPOINTMENT_CONFIRMED => 'Appointment Confirmed',
            self::APPOINTMENT_REMINDER => 'Appointment Reminder',
            self::APPOINTMENT_CANCELLED => 'Appointment Cancelled',
            self::APPOINTMENT_RESCHEDULED => 'Appointment Rescheduled',
            self::PRESCRIPTION_READY => 'Prescription Ready',
            self::TREATMENT_PLAN_APPROVED => 'Treatment Plan Approved',
            self::PAYMENT_RECEIVED => 'Payment Received',
            self::PAYMENT_REMINDER => 'Payment Reminder',
            self::INVOICE_GENERATED => 'Invoice Generated',
            self::GENERAL => 'General',
            self::WAITING_LIST_SLOT => 'Waiting List Slot Available',
            self::BILLING_DUE => 'Billing Due Reminder',
            self::CUSTOM_ANNOUNCEMENT => 'Custom Announcement',
        };
    }

    /** Return all enum values (useful for seeding / validation). */
    public static function dbValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}

