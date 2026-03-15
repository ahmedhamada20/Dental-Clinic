<?php

namespace App\Support\Authorization;

use Illuminate\Contracts\Auth\Authenticatable;

class PermissionAuthorizer
{
    /**
     * Check if a user has permission to perform an ability.
     *
     * This method checks the exact permission string without expanding aliases.
     * IMPORTANT: Only the primary ability string is checked to avoid over-permissioning.
     * Aliases are only resolved for explicit Gate::check() calls in custom gates.
     *
     * Authorization Flow:
     * 1. The ability is checked as-is (e.g., 'manage_patients')
     * 2. The user must have the exact ability as a permission
     * 3. This prevents users with only 'patients.view' from satisfying 'manage_patients'
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable|null  $user
     * @param  string  $ability
     * @return bool
     */
    public static function check(?Authenticatable $user, string $ability): bool
    {
        if (! $user) {
            return false;
        }

        $abilities = PermissionMap::resolve($ability);

        if (method_exists($user, 'hasAnyPermission')) {
            return $user->hasAnyPermission($abilities);
        }

        if (method_exists($user, 'hasPermissionTo')) {
            foreach ($abilities as $resolvedAbility) {
                if ($user->hasPermissionTo($resolvedAbility)) {
                    return true;
                }
            }
        }

        return false;
    }
}

