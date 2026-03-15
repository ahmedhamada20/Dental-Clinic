<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureClinicRole
{
    /**
     * Allow the request only when the authenticated user matches at least one role.
     *
     * Usage: clinic.role:admin,doctor
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || empty($roles)) {
            abort(403);
        }

        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole($roles)) {
            return $next($request);
        }

        $userType = $user->user_type;
        if ($userType instanceof \BackedEnum) {
            $userType = $userType->value;
        }

        if (in_array((string) $userType, $roles, true)) {
            return $next($request);
        }

        abort(403);
    }
}

