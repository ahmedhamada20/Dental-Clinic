<?php

namespace App\Policies\Concerns;

use App\Models\User;

trait InteractsWithClinicRoles
{
    protected function userType(User $user): string
    {
        $type = $user->user_type;

        if ($type instanceof \BackedEnum) {
            return (string) $type->value;
        }

        return is_string($type) ? $type : '';
    }

    protected function hasAnyClinicRole(User $user, array $roles): bool
    {
        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole($roles)) {
            return true;
        }

        return in_array($this->userType($user), $roles, true);
    }

    protected function hasClinicRole(User $user, string $role): bool
    {
        if (method_exists($user, 'hasRole') && $user->hasRole($role)) {
            return true;
        }

        return $this->userType($user) === $role;
    }
}

