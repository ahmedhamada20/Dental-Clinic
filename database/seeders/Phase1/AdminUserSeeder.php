<?php

namespace Database\Seeders\Phase1;

use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
/**
 * AdminUserSeeder
 *
 * Seeds the default admin user for system initialization.
 * Idempotent: Uses firstOrCreate to avoid duplicate admin users.
 */
class AdminUserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */


    public function run(): void
    {
        // ensure admin role exists
        Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web'
        ]);

        $admin = User::firstOrCreate(
            ['email' => 'admin@dentalclinic.local'],
            [
                'first_name' => 'System',
                'last_name' => 'Administrator',
                'full_name' => 'System Administrator',
                'phone' => '+1-800-ADMIN',
                'password' => Hash::make('password'),
                'user_type' => UserType::ADMIN,
                'status' => UserStatus::ACTIVE,
            ]
        );

        $admin->assignRole('admin');
    }
}

