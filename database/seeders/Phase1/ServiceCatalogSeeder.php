<?php

namespace Database\Seeders\Phase1;

use App\Models\Clinic\MedicalSpecialty;
use App\Models\Clinic\Service;
use App\Models\Clinic\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $catalog = [
            'Dental' => [
                [
                    'category' => [
                        'name_ar' => 'تنظيف',
                        'name_en' => 'Cleaning',
                        'sort_order' => 1,
                    ],
                    'service' => [
                        'code' => 'DEN-CLEANING',
                        'name_ar' => 'تنظيف الأسنان',
                        'name_en' => 'Cleaning',
                        'description_ar' => 'جلسة تنظيف احترافية للحفاظ على صحة الأسنان واللثة.',
                        'description_en' => 'Professional dental cleaning session for oral hygiene maintenance.',
                        'default_price' => 150,
                        'duration_minutes' => 45,
                        'sort_order' => 1,
                    ],
                ],
                [
                    'category' => [
                        'name_ar' => 'علاج الجذور',
                        'name_en' => 'Root Canal',
                        'sort_order' => 2,
                    ],
                    'service' => [
                        'code' => 'DEN-ROOT-CANAL',
                        'name_ar' => 'علاج عصب',
                        'name_en' => 'Root Canal',
                        'description_ar' => 'علاج جذور الأسنان للحفاظ على السن المصاب.',
                        'description_en' => 'Endodontic treatment to save an infected tooth.',
                        'default_price' => 450,
                        'duration_minutes' => 90,
                        'sort_order' => 2,
                    ],
                ],
            ],
            'Dermatology' => [
                [
                    'category' => [
                        'name_ar' => 'استشارة جلدية',
                        'name_en' => 'Skin Consultation',
                        'sort_order' => 1,
                    ],
                    'service' => [
                        'code' => 'DERM-SKIN-CONSULT',
                        'name_ar' => 'استشارة جلدية',
                        'name_en' => 'Skin Consultation',
                        'description_ar' => 'تقييم شامل لمشكلات الجلد وخطة علاجية مناسبة.',
                        'description_en' => 'Comprehensive skin assessment with a tailored treatment plan.',
                        'default_price' => 220,
                        'duration_minutes' => 30,
                        'sort_order' => 1,
                    ],
                ],
                [
                    'category' => [
                        'name_ar' => 'جلسة ليزر',
                        'name_en' => 'Laser Session',
                        'sort_order' => 2,
                    ],
                    'service' => [
                        'code' => 'DERM-LASER-SESSION',
                        'name_ar' => 'جلسة ليزر',
                        'name_en' => 'Laser Session',
                        'description_ar' => 'جلسة ليزر تجميلية أو علاجية حسب التشخيص.',
                        'description_en' => 'Therapeutic or cosmetic laser treatment session based on evaluation.',
                        'default_price' => 500,
                        'duration_minutes' => 60,
                        'sort_order' => 2,
                    ],
                ],
            ],
            'Ophthalmology' => [
                [
                    'category' => [
                        'name_ar' => 'فحص العين',
                        'name_en' => 'Eye Examination',
                        'sort_order' => 1,
                    ],
                    'service' => [
                        'code' => 'OPH-EYE-EXAM',
                        'name_ar' => 'فحص العين',
                        'name_en' => 'Eye Examination',
                        'description_ar' => 'فحص شامل لصحة العين وتشخيص المشكلات البصرية.',
                        'description_en' => 'Complete eye health exam and vision problem assessment.',
                        'default_price' => 180,
                        'duration_minutes' => 30,
                        'sort_order' => 1,
                    ],
                ],
                [
                    'category' => [
                        'name_ar' => 'اختبار النظر',
                        'name_en' => 'Vision Test',
                        'sort_order' => 2,
                    ],
                    'service' => [
                        'code' => 'OPH-VISION-TEST',
                        'name_ar' => 'اختبار النظر',
                        'name_en' => 'Vision Test',
                        'description_ar' => 'قياس حدة الإبصار وتحديد الاحتياج للتصحيح البصري.',
                        'description_en' => 'Visual acuity testing and refractive correction assessment.',
                        'default_price' => 120,
                        'duration_minutes' => 20,
                        'sort_order' => 2,
                    ],
                ],
            ],
        ];

        foreach ($catalog as $specialtyName => $items) {
            $specialty = MedicalSpecialty::query()->firstWhere('name', $specialtyName);

            if (! $specialty) {
                continue;
            }

            foreach ($items as $item) {
                $category = ServiceCategory::query()->updateOrCreate(
                    [
                        'medical_specialty_id' => $specialty->id,
                        'name_en' => $item['category']['name_en'],
                    ],
                    [
                        'name_ar' => $item['category']['name_ar'],
                        'is_active' => true,
                        'sort_order' => $item['category']['sort_order'],
                    ]
                );

                Service::query()->updateOrCreate(
                    ['code' => $item['service']['code']],
                    [
                        'category_id' => $category->id,
                        'name_ar' => $item['service']['name_ar'],
                        'name_en' => $item['service']['name_en'],
                        'description_ar' => $item['service']['description_ar'],
                        'description_en' => $item['service']['description_en'],
                        'default_price' => $item['service']['default_price'],
                        'duration_minutes' => $item['service']['duration_minutes'],
                        'is_bookable' => true,
                        'is_active' => true,
                        'sort_order' => $item['service']['sort_order'],
                    ]
                );
            }
        }
    }
}

