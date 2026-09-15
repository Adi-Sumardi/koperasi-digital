<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Settings;

use App\Actions\Admin\Auth\ConfirmTwoFactorSetupAction;
use App\Actions\Admin\Auth\DisableTwoFactorAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Admin\Settings\DisableTwoFactorRequest;
use App\Http\Requests\Web\Admin\Settings\EnableTwoFactorRequest;
use App\Services\Security\TwoFactorAuthenticationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Aktifkan/nonaktifkan 2FA secara sukarela dari halaman pengaturan — terpisah
 * dari alur wajib di kanal login (App\Http\Controllers\Web\Admin\Auth\TwoFactorSetupController)
 * yang berlaku khusus untuk Super Admin yang belum pernah setup sama sekali.
 */
class TwoFactorSettingsController extends Controller
{
    public function create(Request $request, TwoFactorAuthenticationService $totp): View|RedirectResponse
    {
        if ($request->user()->hasEnabledTwoFactor()) {
            return redirect()->route('admin.settings.security.index');
        }

        $secret = $request->session()->get('settings_2fa_setup_secret');
        if (! $secret) {
            $secret = $totp->generateSecretKey();
            $request->session()->put('settings_2fa_setup_secret', $secret);
        }

        return view('admin.auth.two-factor-setup', [
            'qrSvg' => $totp->getQrCodeSvg($request->user(), $secret),
            'secret' => $secret,
            'confirmRoute' => route('admin.settings.security.2fa.enable.store'),
        ]);
    }

    public function store(EnableTwoFactorRequest $request, ConfirmTwoFactorSetupAction $confirm): View|RedirectResponse
    {
        $secret = $request->session()->get('settings_2fa_setup_secret');

        if (! $secret) {
            return redirect()->route('admin.settings.security.2fa.enable');
        }

        $recoveryCodes = $confirm->execute($request->user(), $secret, $request->validated('code'));
        $request->session()->forget('settings_2fa_setup_secret');

        return view('admin.auth.two-factor-recovery-codes', [
            'recoveryCodes' => $recoveryCodes,
            'continueUrl' => route('admin.settings.security.index'),
            'continueLabel' => 'Sudah Disimpan, Kembali ke Pengaturan',
        ]);
    }

    public function destroy(DisableTwoFactorRequest $request, DisableTwoFactorAction $disable): RedirectResponse
    {
        $disable->execute($request->user());

        return redirect()->route('admin.settings.security.index')->with('status', '2FA berhasil dinonaktifkan.');
    }
}
