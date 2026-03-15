<?php

namespace Database\Factories\Patient;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Patient>
 */
class PatientFactory extends Factory
{
    public function definition(): array
    {
        $female = fake()->boolean(45);

        $firstName = $female
            ? fake('ar_EG')->randomElement(['فاطمة', 'نور', 'مريم', 'آية', 'سلمى', 'هدى', 'ريم', 'سارة'])
            : fake('ar_EG')->randomElement(['أحمد', 'محمد', 'عمر', 'يوسف', 'مصطفى', 'محمود', 'عبدالله', 'طارق']);

        $lastName = fake('ar_EG')->randomElement([
            'حسن',
            'عبدالسلام',
            'النجار',
            'الشريف',
            'السيد',
            'عبدالرحمن',
            'فؤاد',
            'منصور',
            'زكي',
            'علي',
        ]);

        $dob = fake()->dateTimeBetween('-65 years', '-7 years');
        $age = (int) now()->diffInYears($dob);

        $phone = '01' . fake()->randomElement(['0', '1', '2', '5']) . fake()->numerify('########');

        return [
            'patient_code' => 'PAT-' . strtoupper(Str::random(8)),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'full_name' => $firstName . ' ' . $lastName,
            'phone' => $phone,
            'alternate_phone' => fake()->boolean(35)
                ? '01' . fake()->randomElement(['0', '1', '2', '5']) . fake()->numerify('########')
                : null,
            'email' => fake()->boolean(70) ? fake()->unique()->safeEmail() : null,
            'password' => Hash::make('patient123'),
            'gender' => $female ? 'female' : 'male',
            'date_of_birth' => $dob->format('Y-m-d'),
            'age' => \Carbon\Carbon::parse($dob)->age,
            'address' => fake('ar_EG')->randomElement([
                '١٢ شارع التحرير، الدقي',
                '٥ شارع جامعة الدول العربية، المهندسين',
                '١٨ شارع الهرم، الجيزة',
                '٩ شارع النصر، مدينة نصر',
                '٤ شارع خالد بن الوليد، الإسكندرية',
                '٢١ شارع الجيش، طنطا',
                '٣ شارع البحر، المنصورة',
            ]),
            'city' => fake('ar_EG')->randomElement([
                'القاهرة',
                'الجيزة',
                'الإسكندرية',
                'المنصورة',
                'طنطا',
                'الزقازيق',
                'أسيوط',
            ]),
            'profile_image' => null,
            'status' => fake()->randomElement(['active', 'active', 'active', 'inactive', 'blocked']),
            'registered_from' => fake()->randomElement(['dashboard', 'dashboard', 'mobile_app']),
            'last_login_at' => fake()->boolean(70) ? fake()->dateTimeBetween('-60 days', 'now') : null,
            'remember_token' => Str::random(10),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (): array => ['status' => 'active']);
    }

    public function mobileApp(): static
    {
        return $this->state(fn (): array => ['registered_from' => 'mobile_app']);
    }
}
