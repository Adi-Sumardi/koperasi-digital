<?php

declare(strict_types=1);

namespace App\Actions\Loans;

use App\Enums\LoanApplicationStatus;
use App\Exceptions\LoanWorkflowException;
use App\Models\LoanApplication;
use App\Models\LoanProduct;
use App\Models\Member;
use App\Services\Audit\AuditLogger;
use App\Services\Loans\LoanEligibilityService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ApplyLoanAction
{
    public function __construct(
        private readonly SimulateLoanAction $simulate,
        private readonly LoanEligibilityService $eligibility,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function execute(
        Member $member,
        LoanProduct $product,
        float $amount,
        int $tenorMonths,
        string $purpose,
        ?string $guaranteeType,
    ): LoanApplication {
        $simulation = $this->simulate->execute($product, $amount, $tenorMonths);

        $maxCeiling = $this->eligibility->maxCeiling($member);
        if ($amount > $maxCeiling) {
            throw new LoanWorkflowException(
                sprintf('Pengajuan melebihi plafon maksimal Anda (3x total simpanan): Rp %s.', number_format($maxCeiling, 2, ',', '.'))
            );
        }

        // Angsuran tertinggi dalam jadwal dipakai untuk uji DSR — konservatif untuk
        // metode sliding, di mana angsuran bulan pertama adalah yang terbesar.
        $highestInstallment = max(array_column($simulation->schedule, 'totalInstallment'));

        if (! $this->eligibility->passesDebtServiceRatio($member, $highestInstallment)) {
            throw new LoanWorkflowException(
                'Total angsuran bulanan melebihi 35% dari gaji pokok bulanan Anda.'
            );
        }

        return DB::transaction(function () use ($member, $product, $amount, $tenorMonths, $purpose, $guaranteeType) {
            $application = LoanApplication::create([
                'member_id' => $member->id,
                'loan_product_id' => $product->id,
                'application_number' => $this->generateApplicationNumber(),
                'amount' => $amount,
                'tenor_months' => $tenorMonths,
                'purpose' => $purpose,
                'guarantee_type' => $guaranteeType,
                'status' => LoanApplicationStatus::PENDING_REVIEW,
            ]);

            $this->auditLogger->log(
                event: 'loan_application.submitted',
                actor: $member->user,
                subject: $application,
                new: ['amount' => $amount, 'tenor_months' => $tenorMonths, 'product' => $product->name],
            );

            return $application;
        });
    }

    private function generateApplicationNumber(): string
    {
        return 'LON-'.now()->format('Ymd').'-'.Str::padLeft((string) random_int(0, 999999), 6, '0');
    }
}
