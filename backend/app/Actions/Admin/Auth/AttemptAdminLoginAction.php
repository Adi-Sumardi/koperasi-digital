<?php

declare(strict_types=1);

namespace App\Actions\Admin\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AttemptAdminLoginAction
{
    /**
     * Portal Web Admin khusus Pengurus & Super Admin; anggota (role member)
     * ditolak sekalipun kredensialnya benar (AGENTS.md §1).
     */
    public function execute(string $email, string $password, bool $remember): User
    {
        if (! Auth::guard('web')->attempt(['email' => $email, 'password' => $password], $remember)) {
            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi salah.',
            ]);
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        if ($user->role === UserRole::MEMBER || ! $user->is_active) {
            Auth::guard('web')->logout();

            throw ValidationException::withMessages([
                'email' => 'Akun ini tidak memiliki akses ke portal Pengurus.',
            ]);
        }

        return $user;
    }
}
