<?php
/**
 * Fix script: Assign Spatie roles to existing users that have no roles.
 * Maps UserType → role name and assigns the correct role.
 */
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Enums\UserType;
use App\Models\User;
use Spatie\Permission\Models\Role;

// Make sure assistant role exists (run RoleAndPermissionSeeder first if needed)
$roles = Role::pluck('name')->toArray();
echo "Available roles: " . implode(', ', $roles) . "\n\n";

if (!in_array('assistant', $roles)) {
    echo "WARNING: 'assistant' role not found. Run: php artisan db:seed --class=Database\\\\Seeders\\\\Phase1\\\\RoleAndPermissionSeeder first.\n";
    exit(1);
}

$roleMap = [
    UserType::ADMIN->value       => 'admin',
    UserType::DOCTOR->value      => 'dentist',
    UserType::RECEPTIONIST->value => 'receptionist',
    UserType::ASSISTANT->value   => 'assistant',
];

$fixed = 0;
$skipped = 0;

$users = User::with('roles')->get();
foreach ($users as $user) {
    if ($user->roles->isNotEmpty()) {
        $skipped++;
        echo "SKIP  ID:{$user->id} {$user->email} (already has: {$user->roles->pluck('name')->join(', ')})\n";
        continue;
    }

    $userType = $user->user_type;
    $userTypeValue = $userType instanceof UserType ? $userType->value : (string) $userType;

    $roleName = $roleMap[$userTypeValue] ?? null;
    if (!$roleName) {
        echo "WARN  ID:{$user->id} {$user->email} unknown user_type: {$userTypeValue}\n";
        continue;
    }

    $user->assignRole($roleName);
    $fixed++;
    echo "FIXED ID:{$user->id} {$user->email} → assigned role '{$roleName}'\n";
}

echo "\n=== DONE: {$fixed} fixed, {$skipped} already had roles ===\n";

// Verify
echo "\n=== VERIFICATION ===\n";
$noRole = User::doesntHave('roles')->count();
echo "Users still without any role: {$noRole}\n";

