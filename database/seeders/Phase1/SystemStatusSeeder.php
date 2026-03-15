<?php

namespace Database\Seeders\Phase1;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * SystemStatusSeeder
 *
 * Documents and validates system status enums used across the clinic.
 * These are PHP enums (not stored in DB), but this seeder validates them.
 *
 * Manages:
 * - Appointment Statuses
 * - Visit Statuses
 * - Invoice Statuses
 * - Payment Methods
 * - User Statuses
 *
 * This seeder is primarily informational and validates enum availability.
 */
class SystemStatusSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Validate all required enums exist and have proper methods
        $this->validateAppointmentStatuses();
        $this->validateVisitStatuses();
        $this->validateInvoiceStatuses();
        $this->validatePaymentMethods();
        $this->validateUserStatuses();
    }

    /**
     * Validate that AppointmentStatus enum has all required cases.
     */
    private function validateAppointmentStatuses(): void
    {
        $requiredCases = [
            'PENDING',
            'CONFIRMED',
            'CHECKED_IN',
            'IN_PROGRESS',
            'COMPLETED',
            'CANCELLED_BY_PATIENT',
            'CANCELLED_BY_ADMIN',
            'NO_SHOW',
        ];

        $enum = \App\Enums\AppointmentStatus::class;
        foreach ($requiredCases as $case) {
            if (!defined("$enum::$case")) {
                throw new \RuntimeException("AppointmentStatus enum missing case: $case");
            }
        }

        // Validate methods exist
        $status = \App\Enums\AppointmentStatus::PENDING;
        if (!method_exists($status, 'label') || !method_exists($status, 'isActive')) {
            throw new \RuntimeException('AppointmentStatus enum missing required methods');
        }
    }

    /**
     * Validate that VisitStatus enum has all required cases.
     */
    private function validateVisitStatuses(): void
    {
        $requiredCases = [
            'SCHEDULED',
            'IN_PROGRESS',
            'COMPLETED',
            'CANCELLED',
        ];

        $enum = \App\Enums\VisitStatus::class;
        foreach ($requiredCases as $case) {
            if (!defined("$enum::$case")) {
                throw new \RuntimeException("VisitStatus enum missing case: $case");
            }
        }

        // Validate methods exist
        $status = \App\Enums\VisitStatus::SCHEDULED;
        if (!method_exists($status, 'label') || !method_exists($status, 'isActive')) {
            throw new \RuntimeException('VisitStatus enum missing required methods');
        }
    }

    /**
     * Validate that InvoiceStatus enum has all required cases.
     */
    private function validateInvoiceStatuses(): void
    {
        $requiredCases = [
            'UNPAID',
            'PARTIALLY_PAID',
            'PAID',
            'CANCELLED',
        ];

        $enum = \App\Enums\InvoiceStatus::class;
        foreach ($requiredCases as $case) {
            if (!defined("$enum::$case")) {
                throw new \RuntimeException("InvoiceStatus enum missing case: $case");
            }
        }

        // Validate methods exist
        $status = \App\Enums\InvoiceStatus::PAID;
        if (!method_exists($status, 'label') || !method_exists($status, 'isPaid') || !method_exists($status, 'isActive')) {
            throw new \RuntimeException('InvoiceStatus enum missing required methods');
        }
    }

    /**
     * Validate that PaymentMethod enum has all required cases.
     */
    private function validatePaymentMethods(): void
    {
        $requiredCases = [
            'CASH',
            'BANK_TRANSFER',
            'INSTAPAY',
            'VODAFONE_CASH',
        ];

        $enum = \App\Enums\PaymentMethod::class;
        foreach ($requiredCases as $case) {
            if (!defined("$enum::$case")) {
                throw new \RuntimeException("PaymentMethod enum missing case: $case");
            }
        }

        // Validate methods exist
        $method = \App\Enums\PaymentMethod::CASH;
        if (!method_exists($method, 'label')) {
            throw new \RuntimeException('PaymentMethod enum missing required methods');
        }
    }

    /**
     * Validate that UserStatus enum has all required cases.
     */
    private function validateUserStatuses(): void
    {
        $requiredCases = [
            'ACTIVE',
            'INACTIVE',
        ];

        $enum = \App\Enums\UserStatus::class;
        foreach ($requiredCases as $case) {
            if (!defined("$enum::$case")) {
                throw new \RuntimeException("UserStatus enum missing case: $case");
            }
        }

        // Validate methods exist
        $status = \App\Enums\UserStatus::ACTIVE;
        if (!method_exists($status, 'label') || !method_exists($status, 'isActive')) {
            throw new \RuntimeException('UserStatus enum missing required methods');
        }
    }
}

