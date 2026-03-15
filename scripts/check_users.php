<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== USERS & ROLES ===\n";
$users = App\Models\User::with('roles.permissions')->get();
foreach ($users as $u) {
    $roles = $u->roles->pluck('name')->join(', ') ?: 'NO ROLES';
    echo "ID:{$u->id} | {$u->email} | Roles: {$roles}\n";
}

echo "\n=== ALL ROLES & PERMISSIONS ===\n";
$roles = Spatie\Permission\Models\Role::with('permissions')->get();
foreach ($roles as $r) {
    $perms = $r->permissions->pluck('name')->join(', ') ?: 'NO PERMISSIONS';
    echo "Role: {$r->name} | Permissions: {$perms}\n";
}

echo "\n=== CHECK dashboard.view PERMISSION ===\n";
$perm = Spatie\Permission\Models\Permission::where('name', 'dashboard.view')->first();
if ($perm) {
    echo "dashboard.view permission exists (id={$perm->id})\n";
    $usersWithPerm = App\Models\User::permission('dashboard.view')->get();
    echo "Users with dashboard.view: " . $usersWithPerm->pluck('email')->join(', ') . "\n";
} else {
    echo "dashboard.view permission does NOT exist!\n";
}

echo "\n=== ALL PERMISSIONS (first 50) ===\n";
$perms = Spatie\Permission\Models\Permission::orderBy('name')->take(50)->pluck('name');
echo $perms->join(', ') . "\n";

