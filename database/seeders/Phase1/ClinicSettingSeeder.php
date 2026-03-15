<?php

namespace Database\Seeders\Phase1;

use App\Models\Clinic\ClinicSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * ClinicSettingSeeder
 *
 * Seeds core clinic configuration settings stored in the database.
 * These are key-value settings that control clinic behavior and defaults.
 * Idempotent: Uses updateOrCreate to safely re-run.
 */
class ClinicSettingSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = $this->getClinicSettings();

        foreach ($settings as $setting) {
            ClinicSetting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }
    }

    /**
     * Get all clinic settings to be seeded.
     *
     * @return array
     */
    private function getClinicSettings(): array
    {
        return [
            // Clinic Basic Information
            [
                'key' => 'clinic_name',
                'value' => 'Bright Smile Dental Clinic',
            ],
            [
                'key' => 'clinic_phone',
                'value' => '+1-555-0100',
            ],
            [
                'key' => 'clinic_email',
                'value' => 'info@brightsmile.clinic',
            ],
            [
                'key' => 'clinic_address',
                'value' => '123 Dental Street, Healthcare City, HC 12345',
            ],
            [
                'key' => 'clinic_timezone',
                'value' => 'America/Chicago',
            ],

            // Appointment Settings
            [
                'key' => 'appointment_slot_duration_minutes',
                'value' => '30',
            ],
            [
                'key' => 'appointment_buffer_minutes',
                'value' => '5',
            ],
            [
                'key' => 'appointment_advance_booking_days',
                'value' => '60',
            ],
            [
                'key' => 'appointment_cancellation_notice_hours',
                'value' => '24',
            ],

            // Visit Settings
            [
                'key' => 'visit_max_wait_time_minutes',
                'value' => '30',
            ],
            [
                'key' => 'visit_allow_check_in_before_minutes',
                'value' => '15',
            ],

            // Payment Settings
            [
                'key' => 'payment_default_currency',
                'value' => 'USD',
            ],
            [
                'key' => 'payment_invoice_prefix',
                'value' => 'INV',
            ],
            [
                'key' => 'payment_invoice_next_number',
                'value' => '1000',
            ],
            [
                'key' => 'payment_default_method',
                'value' => 'cash',
            ],
            [
                'key' => 'payment_due_days',
                'value' => '30',
            ],

            // Notification Settings
            [
                'key' => 'notification_send_appointment_reminders',
                'value' => 'true',
            ],
            [
                'key' => 'notification_reminder_hours_before',
                'value' => '24',
            ],
            [
                'key' => 'notification_send_payment_reminders',
                'value' => 'true',
            ],
            [
                'key' => 'notification_payment_reminder_days_overdue',
                'value' => '7',
            ],

            // System Defaults
            [
                'key' => 'system_records_per_page',
                'value' => '25',
            ],
            [
                'key' => 'system_date_format',
                'value' => 'mm/dd/yyyy',
            ],
            [
                'key' => 'system_time_format',
                'value' => 'HH:mm',
            ],

            // Treatment Settings
            [
                'key' => 'treatment_default_warranty_days',
                'value' => '365',
            ],
        ];
    }
}

