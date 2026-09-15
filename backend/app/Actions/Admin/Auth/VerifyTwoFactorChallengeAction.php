<?php

declare(strict_types=1);

namespace App\Actions\Admin\Auth;

use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Security\TwoFactorAuthenticationService;
use Illuminate\Validation\ValidationException;

class VerifyTwoFactorChallengeAction
{
    public function __construct(
        private readonly TwoFactorAuthenticationService $totp,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function execute(User $user, string $code): void
    {
        if ($this->totp->verify($user->two_factor_secret, $code)) {
            $this->auditLogger->log(event: 'admin.2fa_challenge_passed', actor: $user);

            return;
        }

        $remainingHashes = $this->totp->consumeRecoveryCode($user->two_factor_recovery_codes ?? [], $code);

        if ($remainingHashes !== null) {
            $user->forceFill(['two_factor_recovery_codes' => $remainingHashes])->save();

            $this->auditLogger->log(event: 'admin.2fa_recovery_code_used', actor: $user);

            return;
        }

        $this->auditLogger->log(event: 'admin.2fa_challenge_failed', actor: $user);

        throw ValidationException::withMessages([
            'code' => 'Kode 2FA atau kode pemulihan tidak valid.',
        ]);
    }
}
