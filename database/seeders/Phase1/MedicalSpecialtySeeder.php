<?php

namespace Database\Seeders\Phase1;

use App\Models\Clinic\MedicalSpecialty;
use Illuminate\Database\Seeder;

class MedicalSpecialtySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $specialties = [
            [
                'name' => 'Dental',
                'description' => 'Oral health, teeth, and related maxillofacial care.',
                'is_active' => true,
            ],
            [
                'name' => 'Dermatology',
                'description' => 'Skin, hair, and nail disorder diagnosis and treatment.',
                'is_active' => true,
            ],
            [
                'name' => 'Ophthalmology',
                'description' => 'Eye and vision care including medical and surgical treatment.',
                'is_active' => true,
            ],
            [
                'name' => 'Internal Medicine',
                'description' => 'Comprehensive adult disease prevention, diagnosis, and management.',
                'is_active' => true,
            ],
            [
                'name' => 'Pediatrics',
                'description' => 'Medical care for infants, children, and adolescents.',
                'is_active' => true,
            ],
            [
                'name' => 'Orthopedics',
                'description' => 'Musculoskeletal system care including bones, joints, and ligaments.',
                'is_active' => true,
            ],
        ];

        foreach ($specialties as $specialty) {
            MedicalSpecialty::query()->updateOrCreate(
                ['name' => $specialty['name']],
                $specialty
            );
        }
    }
}

