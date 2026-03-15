<?php

namespace App\Policies;

use App\Models\Billing\Promotion;
use App\Models\User;

/**
 * Class PromotionPolicy
 *
 * Authorization policy for Promotion model.
 */
class PromotionPolicy
{
    private function userType(User $user): string
    {
        $type = $user->user_type;

        if ($type instanceof \BackedEnum) {
            return (string) $type->value;
        }

        return is_string($type) ? $type : '';
    }

    /**
     * Determine if the user can view any promotions.
     */
    public function viewAny(User $user): bool
    {
        return in_array($this->userType($user), ['admin', 'assistant', 'receptionist'], true);
    }

    /**
     * Determine if the user can view the promotion.
     */
    public function view(User $user, Promotion $promotion): bool
    {
        return in_array($this->userType($user), ['admin', 'assistant', 'receptionist'], true);
    }

    /**
     * Determine if the user can create promotions.
     */
    public function create(User $user): bool
    {
        return in_array($this->userType($user), ['admin', 'assistant'], true);
    }

    /**
     * Determine if the user can update the promotion.
     */
    public function update(User $user, Promotion $promotion): bool
    {
        return in_array($this->userType($user), ['admin', 'assistant'], true);
    }

    /**
     * Determine if the user can delete the promotion.
     */
    public function delete(User $user, Promotion $promotion): bool
    {
        return $this->userType($user) === 'admin';
    }

    /**
     * Determine if the user can activate the promotion.
     */
    public function activate(User $user, Promotion $promotion): bool
    {
        return in_array($this->userType($user), ['admin', 'assistant'], true);
    }

    /**
     * Determine if the user can deactivate the promotion.
     */
    public function deactivate(User $user, Promotion $promotion): bool
    {
        return in_array($this->userType($user), ['admin', 'assistant'], true);
    }
}

