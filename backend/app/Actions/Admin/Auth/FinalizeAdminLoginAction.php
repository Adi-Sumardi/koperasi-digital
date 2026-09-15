<?php

declare(strict_types=1);

namespace App\Actions\Admin\Auth;

use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Titik tunggal untuk benar-benar membuat sesi Web Admin — dipanggil baik
 * langsung (Pengurus non-superadmin) maupun setelah tantangan 2FA berhasil
 * (Super Admin), supaya audit log "login berhasil" hanya tercatat sekali,
 * tepat saat sesi sungguhan terbentuk.
 */
class FinalizeAdminLoginAction
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    public function execute(Request $request, User $user, bool $remember): void
    {
        Auth::guard('web')->login($user, $remember);
        $request->session()->regenerate();
        $request->session()->forget(['admin_2fa_pending_user_id', 'admin_2fa_remember', 'admin_2fa_setup_secret']);

        $this->auditLogger->log(event: 'admin.login_succeeded', actor: $user);
    }
}
