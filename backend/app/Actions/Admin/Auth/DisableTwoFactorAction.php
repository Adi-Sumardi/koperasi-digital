<?php

declare(strict_types=1);

namespace App\Actions\Admin\Auth;

use App\Models\User;
use App\Services\Audit\AuditLogger;

class DisableTwoFactorAction
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    public function execute(User $user): void
    {
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        $this->auditLogger->log(event: 'admin.2fa_disabled', actor: $user, subject: $user);
    }
}
