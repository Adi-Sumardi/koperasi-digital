<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Auth;

use App\Actions\Admin\Auth\AttemptAdminLoginAction;
use App\Actions\Admin\Auth\FinalizeAdminLoginAction;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Admin\Auth\AdminLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('admin.auth.login');
    }

    public function store(
        AdminLoginRequest $request,
        AttemptAdminLoginAction $attemptLogin,
        FinalizeAdminLoginAction $finalizeLogin,
    ): RedirectResponse {
        $user = $attemptLogin->execute(
            email: $request->validated('email'),
            password: $request->validated('password'),
        );

        $remember = (bool) $request->validated('remember');

        // Super Admin wajib 2FA (rules/security.md §1): sesi web BELUM dibuat di
        // sini — baru dibentuk oleh FinalizeAdminLoginAction setelah tantangan
        // (atau pengaturan awal) 2FA berhasil dilalui.
        if ($user->role === UserRole::SUPERADMIN) {
            $request->session()->put('admin_2fa_pending_user_id', $user->id);
            $request->session()->put('admin_2fa_remember', $remember);

            return redirect()->route(
                $user->hasEnabledTwoFactor() ? 'admin.2fa.challenge' : 'admin.2fa.setup'
            );
        }

        $finalizeLogin->execute($request, $user, $remember);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
