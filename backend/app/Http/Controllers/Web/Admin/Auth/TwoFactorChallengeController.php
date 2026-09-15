<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Auth;

use App\Actions\Admin\Auth\FinalizeAdminLoginAction;
use App\Actions\Admin\Auth\VerifyTwoFactorChallengeAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Admin\Auth\TwoFactorChallengeRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TwoFactorChallengeController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        $user = $this->pendingUser($request);

        if (! $user instanceof User) {
            return redirect()->route('admin.login');
        }

        if (! $user->hasEnabledTwoFactor()) {
            return redirect()->route('admin.2fa.setup');
        }

        return view('admin.auth.two-factor-challenge');
    }

    public function store(
        TwoFactorChallengeRequest $request,
        VerifyTwoFactorChallengeAction $verify,
        FinalizeAdminLoginAction $finalizeLogin,
    ): RedirectResponse {
        $user = $this->pendingUser($request);

        if (! $user instanceof User) {
            return redirect()->route('admin.login');
        }

        $verify->execute($user, $request->validated('code'));

        $remember = (bool) $request->session()->get('admin_2fa_remember', false);
        $finalizeLogin->execute($request, $user, $remember);

        return redirect()->intended(route('admin.dashboard'));
    }

    private function pendingUser(Request $request): ?User
    {
        $userId = $request->session()->get('admin_2fa_pending_user_id');

        return $userId ? User::find($userId) : null;
    }
}
