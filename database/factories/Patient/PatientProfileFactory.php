<?php

namespace Database\Factories\Patient;


use App\Models\Patient\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PatientProfile>
 */
class PatientProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'occupation' => fake('ar_EG')->randomElement([
                'مهندس',
                'طبيب',
                'مدرس',
                'محاسب',
                'طالب',
                'موظف بنك',
                'صيدلي',
                'مطور برمجيات',
                'صاحب مشروع',
            ]),
            'marital_status' => fake('ar_EG')->randomElement([
                'أعزب',
                'متزوج',
                'مطلق',
                'أرمل',
            ]),
            'preferred_language' => fake()->randomElement(['ar', 'ar', 'ar', 'en']),
            'blood_group' => fake()->randomElement(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']),
            'notes' => fake('ar_EG')->optional(0.65)->randomElement([
                'يفضل المواعيد المسائية بعد الساعة ٦.',
                'حساس جدا من الانتظار الطويل.',
                'يحتاج تذكير قبل الموعد بيوم.',
                'يفضل التواصل عبر واتساب.',
                'يراجع العيادة بشكل دوري كل ٦ أشهر.',
            ]),
        ];
    }
}
