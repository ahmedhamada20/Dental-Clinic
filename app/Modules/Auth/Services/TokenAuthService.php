<?php

namespace App\Modules\Auth\Services;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\NewAccessToken;

class TokenAuthService
{
    /**
     * Create a new Sanctum token for the given user.
     *
     * @param Model $user The authenticatable model (Patient or User)
     * @param string $deviceName The device name for the token
     * @param array $abilities Token abilities (default: ['*'])
     * @return NewAccessToken
     */
    public function createToken(
        Model $user,
        string $deviceName = 'mobile',
        array $abilities = ['*']
    ): NewAccessToken {
        return $user->createToken($deviceName, $abilities);
    }

    /**
     * Revoke the current access token.
     *
     * @param Model $user The authenticatable model
     * @return bool
     */
    public function revokeCurrentToken(Model $user): bool
    {
        if ($user->currentAccessToken()) {
            return $user->currentAccessToken()->delete();
        }

        return false;
    }

    /**
     * Revoke all tokens for the given user.
     *
     * @param Model $user The authenticatable model
     * @return int Number of tokens deleted
     */
    public function revokeAllTokens(Model $user): int
    {
        return $user->tokens()->delete();
    }

    /**
     * Get plain text token from NewAccessToken instance.
     *
     * @param NewAccessToken $accessToken
     * @return string
     */
    public function getPlainTextToken(NewAccessToken $accessToken): string
    {
        return $accessToken->plainTextToken;
    }
}

