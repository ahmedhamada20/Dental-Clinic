<?php

namespace Database\Factories\Patient;


use App\Models\Patient\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PatientMedicalHistory>
 */
class PatientMedicalHistoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'allergies' => fake('ar_EG')->optional(0.55)->randomElement([
                'حساسية من البنسلين',
                'حساسية من اللاتكس',
                'حساسية من بعض المسكنات',
                'لا توجد حساسية معروفة',
            ]),
            'chronic_diseases' => fake('ar_EG')->optional(0.6)->randomElement([
                'سكري من النوع الثاني',
                'ضغط دم مرتفع',
                'قصور بالغدة الدرقية',
                'ربو شعبي',
                'لا يوجد',
            ]),
            'current_medications' => fake('ar_EG')->optional(0.6)->randomElement([
                'ميتفورمين 500mg',
                'كونكور 5mg',
                'إليتروكسين 50mcg',
                'فيتامين د أسبوعي',
                'لا يتناول أدوية منتظمة',
            ]),
            'medical_notes' => fake('ar_EG')->optional(0.65)->sentence(),
            'dental_history' => fake('ar_EG')->optional(0.8)->randomElement([
                'خلع ضرس العقل السفلي منذ عامين.',
                'حشو عصب بالضاحك العلوي الأيمن.',
                'تنظيف جير كل ٨-١٢ شهر.',
                'تركيبة زيركون منذ ٦ أشهر.',
                'لا يوجد تاريخ علاجي كبير.',
            ]),
            'important_alerts' => fake('ar_EG')->optional(0.35)->randomElement([
                'يرجى قياس الضغط قبل أي إجراء.',
                'يخاف من الحقن ويحتاج طمأنة.',
                'تجنب المضادات الحيوية من عائلة البنسلين.',
                'يحتاج مرافق أثناء الزيارات الطويلة.',
            ]),
            'updated_by' => User::query()->inRandomOrder()->value('id'),
        ];
    }
}
