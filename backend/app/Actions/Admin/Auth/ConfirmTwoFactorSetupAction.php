<?php

declare(strict_types=1);

namespace App\Actions\Admin\Auth;

use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Security\TwoFactorAuthenticationService;
use Illuminate\Validation\ValidationException;

class ConfirmTwoFactorSetupAction
{
    public function __construct(
        private readonly TwoFactorAuthenticationService $totp,
        private readonly AuditLogger $auditLogger,
    ) {}

    /**
     * @return array<int, string> Kode pemulihan plaintext — hanya ditampilkan sekali.
     */
    public function execute(User $user, string $pendingSecret, string $code): array
    {
        if (! $this->totp->verify($pendingSecret, $code)) {
            throw ValidationException::withMessages([
                'code' => 'Kode verifikasi tidak valid. Coba lagi.',
            ]);
        }

        $recoveryCodes = $this->totp->generateRecoveryCodes();

        $user->forceFill([
            'two_factor_secret' => $pendingSecret,
            'two_factor_recovery_codes' => $this->totp->hashRecoveryCodes($recoveryCodes),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->auditLogger->log(event: 'admin.2fa_enabled', actor: $user, subject: $user);

        return $recoveryCodes;
    }
}
