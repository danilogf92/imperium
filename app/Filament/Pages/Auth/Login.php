<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    protected function throwFailureValidationException(): never
    {
        $email = strtolower(trim((string) ($this->data['email'] ?? '')));
        $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();

        if ($user && ! $user->is_active) {
            throw ValidationException::withMessages([
                'data.email' => 'Your account is disabled. Contact an administrator to enable it.',
            ]);
        }

        parent::throwFailureValidationException();
    }
}
