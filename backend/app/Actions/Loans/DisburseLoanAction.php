<?php

declare(strict_types=1);

namespace App\Actions\Loans;

use App\Enums\LoanStatus;
use App\Events\Loans\LoanDisbursedEvent;
use App\Models\Loan;
use App\Models\LoanApplication;
use App\Services\Audit\AuditLogger;
use App\Services\Loans\LoanCalculationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DisburseLoanAction
{
    public function __construct(
        private readonly LoanCalculationService $calculator,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function execute(LoanApplication $application): Loan
    {
        return DB::transaction(function () use ($application) {
            $product = $application->loanProduct;
            $monthlyRate = $product->monthlyInterestRate();
            $amount = (float) $application->amount;

            $simulation = $product->interest_type->value === 'flat'
                ? $this->calculator->calculateFlat($amount, $application->tenor_months, $monthlyRate)
                : $this->calculator->calculateSliding($amount, $application->tenor_months, $monthlyRate);

            $disbursedAt = now();

            $loan = Loan::create([
                'member_id' => $application->member_id,
                'loan_application_id' => $application->id,
                'loan_number' => $this->generateLoanNumber(),
                'principal_amount' => $amount,
                'interest_rate' => $product->annual_interest_rate,
                'tenor_months' => $application->tenor_months,
                'monthly_installment' => $simulation->schedule[0]->totalInstallment,
                'outstanding_balance' => $amount,
                'status' => LoanStatus::ACTIVE,
                'disbursed_at' => $disbursedAt,
            ]);

            foreach ($simulation->schedule as $line) {
                $loan->installments()->create([
                    'installment_number' => $line->month,
                    'due_date' => $disbursedAt->copy()->addMonthsNoOverflow($line->month)->toDateString(),
                    'principal_portion' => $line->principalPortion,
                    'interest_portion' => $line->interestPortion,
                    'total_installment' => $line->totalInstallment,
                ]);
            }

            $this->auditLogger->log(
                event: 'loan.disbursed',
                actor: null, // dipicu otomatis begitu jenjang persetujuan terpenuhi, bukan aksi satu orang
                subject: $loan,
                new: [
                    'loan_number' => $loan->loan_number,
                    'principal_amount' => $amount,
                    'tenor_months' => $application->tenor_months,
                    'loan_application_id' => $application->id,
                ],
            );

            LoanDisbursedEvent::dispatch($loan);

            return $loan;
        });
    }

    private function generateLoanNumber(): string
    {
        return 'TRX-LON-'.now()->format('Ymd').'-'.Str::padLeft((string) random_int(0, 999999), 6, '0');
    }
}
