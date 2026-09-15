<?php

declare(strict_types=1);

namespace App\Http\Requests\Web\Admin\Loans;

use App\Enums\LoanApprovalDecision;
use App\Models\LoanApplication;
use App\Services\Loans\LoanApprovalTierService;
use App\Services\Security\TwoFactorAuthenticationService;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DecideLoanApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(LoanApprovalTierService $tierService): array
    {
        $rules = [
            'decision' => ['required', Rule::in(['approved', 'rejected'])],
            'notes' => ['nullable', 'string', 'max:255'],
        ];

        if ($this->requiresTwoFactorStepUp($tierService)) {
            $rules['totp_code'] = ['required', 'string', 'max:32', $this->totpCodeRule()];
        }

        return $rules;
    }

    /**
     * Jenjang nominal tertinggi (>Rp25jt) mewajibkan verifikasi 2FA tambahan saat
     * MENYETUJUI, sesuai rules/security.md §1 ("...persetujuan pencairan pinjaman
     * bernilai besar"). Penolakan tidak mencairkan dana, jadi tidak diwajibkan.
     */
    private function requiresTwoFactorStepUp(LoanApprovalTierService $tierService): bool
    {
        if ($this->input('decision') !== LoanApprovalDecision::APPROVED->value) {
            return false;
        }

        /** @var LoanApplication|null $application */
        $application = $this->route('loanApplication');

        if (! $application) {
            return false;
        }

        return $tierService->resolve((float) $application->amount)->requiresTwoFactor;
    }

    private function totpCodeRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $user = $this->user();

            if (! $user->hasEnabledTwoFactor()) {
                $fail('Anda harus mengaktifkan 2FA terlebih dahulu untuk menyetujui pinjaman pada jenjang nominal ini.');

                return;
            }

            if (! app(TwoFactorAuthenticationService::class)->verify($user->two_factor_secret, (string) $value)) {
                $fail('Kode 2FA tidak valid.');
            }
        };
    }
}
