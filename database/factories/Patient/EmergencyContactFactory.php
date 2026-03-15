<?php

namespace Database\Factories\Patient;

use App\Models\Patient\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmergencyContact>
 */
class EmergencyContactFactory extends Factory
{
    public function definition(): array
    {
        $name = fake('ar_EG')->randomElement([
            'محمد حسن',
            'ندى علي',
            'سارة السيد',
            'أحمد فؤاد',
            'هدى عبدالسلام',
            'محمود منصور',
            'مريم زكي',
            'طارق عبدالله',
        ]);

        return [
            'patient_id' => Patient::factory(),
            'name' => $name,
            'relation' => fake('ar_EG')->randomElement([
                'الزوج',
                'الزوجة',
                'الأب',
                'الأم',
                'الأخ',
                'الأخت',
                'الابن',
                'الابنة',
                'صديق',
            ]),
            'phone' => '01' . fake()->randomElement(['0', '1', '2', '5']) . fake()->numerify('########'),
            'notes' => fake('ar_EG')->optional(0.45)->randomElement([
                'متاح بعد الساعة ٤ مساء.',
                'يفضل الاتصال المباشر في الحالات الطارئة.',
                'يرد بسرعة على واتساب.',
                'يسكن بالقرب من العيادة.',
            ]),
        ];
    }
}
