<?php

declare(strict_types=1);

namespace App\Actions\Admin\Auth;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AttemptAdminLoginAction
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    /**
     * Hanya memverifikasi kredensial & kelayakan akses — TIDAK membuat sesi.
     * Super Admin masih harus melewati tantangan 2FA (lihat FinalizeAdminLoginAction)
     * sebelum sesi web sungguhan terbentuk (rules/security.md §1).
     */
    public function execute(string $email, string $password): User
    {
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            $this->auditLogger->log(event: 'admin.login_failed', actor: $user, new: ['email' => $email]);

            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi salah.',
            ]);
        }

        if ($user->role === UserRole::MEMBER || ! $user->is_active) {
            $this->auditLogger->log(event: 'admin.login_failed', actor: $user, new: ['reason' => 'no_portal_access']);

            throw ValidationException::withMessages([
                'email' => 'Akun ini tidak memiliki akses ke portal Pengurus.',
            ]);
        }

        return $user;
    }
}
