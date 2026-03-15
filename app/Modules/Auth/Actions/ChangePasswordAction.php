<?php

namespace App\Modules\Auth\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ChangePasswordAction
{
    /**
     * Execute the change password action.
     *
     * @param Model $user The authenticated patient or user
     * @param array $data The validated data containing current_password and password
     * @return bool
     * @throws ValidationException
     */
    public function execute(Model $user, array $data): bool
    {
        // Verify current password
        if (!Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        // Update to new password
        return $user->update([
            'password' => Hash::make($data['password']),
        ]);
    }
}

