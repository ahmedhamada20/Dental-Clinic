<?php

namespace App\Modules\Auth\Actions;

use App\Modules\Auth\Services\TokenAuthService;
use Illuminate\Database\Eloquent\Model;

class LogoutAction
{
    public function __construct(
        private readonly TokenAuthService $tokenService
    ) {
    }

    /**
     * Execute the logout action.
     * Revokes the current access token for the authenticated user.
     *
     * @param Model $user The authenticated patient or user
     * @return bool
     */
    public function execute(Model $user): bool
    {
        return $this->tokenService->revokeCurrentToken($user);
    }

    /**
     * Execute logout and revoke all tokens.
     *
     * @param Model $user The authenticated patient or user
     * @return int Number of tokens revoked
     */
    public function executeAll(Model $user): int
    {
        return $this->tokenService->revokeAllTokens($user);
    }
}

