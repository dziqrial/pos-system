<?php

namespace Modules\System\Services;

use Illuminate\Support\Facades\Auth;
use Modules\System\Models\User;

class AuthService
{
    public function login(string $email, string $password, bool $remember = false): bool
    {
        return Auth::attempt(
            ['email' => $email, 'password' => $password, 'is_active' => true],
            $remember
        );
    }

    public function logout(): void
    {
        Auth::logout();
    }

    public function createUser(array $data): User
    {
        return User::create([
            'store_id'  => $data['store_id'],
            'role_id'   => $data['role_id'] ?? null,
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => $data['password'],
            'pin'       => $data['pin'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }
}
