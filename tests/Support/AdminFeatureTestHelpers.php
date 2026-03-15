<?php

namespace Tests\Support;

use App\Models\Clinic\MedicalSpecialty;
use App\Models\Clinic\Service;
use App\Models\Clinic\ServiceCategory;
use App\Models\Patient\Patient;
use App\Models\User;
use Database\Seeders\Test\AdminFeaturePermissionSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

trait AdminFeatureTestHelpers
{
    protected function seedAdminFeaturePermissions(): array
    {
        $this->seed(AdminFeaturePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return AdminFeaturePermissionSeeder::permissions();
    }

    protected function createAdminUser(array $permissions = [], array $attributes = []): User
    {
        $suffix = Str::lower(Str::random(10));

        $user = User::query()->create(array_merge([
            'first_name' => 'Admin',
            'last_name' => 'Tester',
            'full_name' => 'Admin Tester',
            'email' => "admin-{$suffix}@example.com",
            'phone' => (string) random_int(5000000000, 5999999999),
            'password' => Hash::make('password'),
            'user_type' => 'admin',
            'status' => 'active',
        ], $attributes));

        if ($permissions !== []) {
            $user->givePermissionTo($permissions);
        }

        return $user;
    }

    protected function createPatient(array $overrides = []): Patient
    {
        $suffix = Str::lower(Str::random(8));

        return Patient::query()->create(array_merge([
            'patient_code' => 'PAT-' . strtoupper(Str::random(8)),
            'first_name' => 'Patient',
            'last_name' => ucfirst($suffix),
            'full_name' => 'Patient ' . ucfirst($suffix),
            'phone' => (string) random_int(6000000000, 6999999999),
            'email' => "patient-{$suffix}@example.com",
            'password' => Hash::make('patient12345'),
            'gender' => 'female',
            'date_of_birth' => now()->subYears(25)->toDateString(),
            'age' => 25,
            'address' => 'Main street',
            'city' => 'Cairo',
            'status' => 'active',
            'registered_from' => 'dashboard',
        ], $overrides));
    }

    protected function createSpecialtyCategoryService(): array
    {
        $specialty = MedicalSpecialty::query()->create([
            'name' => 'General Dentistry',
            'description' => 'General cases',
            'is_active' => true,
        ]);

        $category = ServiceCategory::query()->create([
            'medical_specialty_id' => $specialty->id,
            'name_ar' => 'تنظيف',
            'name_en' => 'Cleaning',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $service = Service::query()->create([
            'category_id' => $category->id,
            'name_ar' => 'تنظيف الأسنان',
            'name_en' => 'Teeth Cleaning',
            'default_price' => 150,
            'duration_minutes' => 30,
            'is_bookable' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        return [$specialty, $category, $service];
    }

    protected function createDentist(MedicalSpecialty $specialty, array $overrides = []): User
    {
        $suffix = Str::lower(Str::random(10));

        return User::query()->create(array_merge([
            'first_name' => 'Dentist',
            'last_name' => 'User',
            'full_name' => 'Dentist User',
            'email' => "dentist-{$suffix}@example.com",
            'phone' => (string) random_int(7000000000, 7999999999),
            'password' => Hash::make('password'),
            'user_type' => 'doctor',
            'status' => 'active',
            'specialty_id' => $specialty->id,
        ], $overrides));
    }
}

