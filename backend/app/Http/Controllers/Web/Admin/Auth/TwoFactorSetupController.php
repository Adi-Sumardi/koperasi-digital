<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Auth;

use App\Actions\Admin\Auth\ConfirmTwoFactorSetupAction;
use App\Actions\Admin\Auth\FinalizeAdminLoginAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Admin\Auth\ConfirmTwoFactorRequest;
use App\Models\User;
use App\Services\Security\TwoFactorAuthenticationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TwoFactorSetupController extends Controller
{
    public function create(Request $request, TwoFactorAuthenticationService $totp): View|RedirectResponse
    {
        $user = $this->pendingUser($request);

        if (! $user instanceof User) {
            return redirect()->route('admin.login');
        }

        if ($user->hasEnabledTwoFactor()) {
            return redirect()->route('admin.2fa.challenge');
        }

        // Rahasia dipegang di sesi selama proses setup — baru ditulis ke database
        // setelah kode konfirmasi pertama berhasil diverifikasi.
        $secret = $request->session()->get('admin_2fa_setup_secret');
        if (! $secret) {
            $secret = $totp->generateSecretKey();
            $request->session()->put('admin_2fa_setup_secret', $secret);
        }

        return view('admin.auth.two-factor-setup', [
            'qrSvg' => $totp->getQrCodeSvg($user, $secret),
            'secret' => $secret,
        ]);
    }

    public function store(
        ConfirmTwoFactorRequest $request,
        ConfirmTwoFactorSetupAction $confirm,
        FinalizeAdminLoginAction $finalizeLogin,
    ): View|RedirectResponse {
        $user = $this->pendingUser($request);
        $secret = $request->session()->get('admin_2fa_setup_secret');

        if (! $user instanceof User || ! $secret) {
            return redirect()->route('admin.login');
        }

        $recoveryCodes = $confirm->execute($user, $secret, $request->validated('code'));
        $remember = (bool) $request->session()->get('admin_2fa_remember', false);

        $finalizeLogin->execute($request, $user, $remember);

        return view('admin.auth.two-factor-recovery-codes', ['recoveryCodes' => $recoveryCodes]);
    }

    private function pendingUser(Request $request): ?User
    {
        $userId = $request->session()->get('admin_2fa_pending_user_id');

        return $userId ? User::find($userId) : null;
    }
}
