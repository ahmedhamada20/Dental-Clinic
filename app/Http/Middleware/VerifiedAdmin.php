<?php

namespace App\Http\Middleware;

use App\Enums\UserType;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifiedAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        /** @var \App\Models\User $user */
        $user = auth()->user();

        $type = $user->user_type;

        if ($type !== UserType::ADMIN && !in_array($type, [UserType::DOCTOR, UserType::RECEPTIONIST, UserType::ASSISTANT], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
            ], 403);
        }

        if ($user->status !== \App\Enums\UserStatus::ACTIVE) {
            return response()->json([
                'success' => false,
                'message' => 'User account is not active',
            ], 403);
        }

        return $next($request);
    }
}

