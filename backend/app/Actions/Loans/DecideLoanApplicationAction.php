<?php

declare(strict_types=1);

namespace App\Actions\Loans;

use App\Enums\LoanApplicationStatus;
use App\Enums\LoanApprovalDecision;
use App\Exceptions\LoanWorkflowException;
use App\Models\LoanApplication;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Loans\LoanApprovalTierService;
use Illuminate\Support\Facades\DB;

class DecideLoanApplicationAction
{
    public function __construct(
        private readonly LoanApprovalTierService $tierService,
        private readonly DisburseLoanAction $disburse,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function execute(
        LoanApplication $application,
        User $approver,
        LoanApprovalDecision $decision,
        ?string $notes,
    ): LoanApplication {
        return DB::transaction(function () use ($application, $approver, $decision, $notes) {
            $application = LoanApplication::lockForUpdate()->findOrFail($application->id);

            if ($application->status !== LoanApplicationStatus::PENDING_REVIEW) {
                throw new LoanWorkflowException('Pengajuan ini sudah diputuskan sebelumnya.');
            }

            $tier = $this->tierService->resolve((float) $application->amount);

            if (! in_array($approver->role->value, $tier->roles, true)) {
                throw new LoanWorkflowException('Anda tidak memiliki wewenang untuk menyetujui pengajuan pada jenjang nominal ini.');
            }

            if ($application->approvals()->where('approver_id', $approver->id)->exists()) {
                throw new LoanWorkflowException('Anda sudah memberikan keputusan untuk pengajuan ini.');
            }

            $approval = $application->approvals()->create([
                'approver_id' => $approver->id,
                'decision' => $decision,
                'notes' => $notes,
            ]);

            $this->auditLogger->log(
                event: 'loan_application.decision_recorded',
                actor: $approver,
                subject: $approval,
                new: ['decision' => $decision->value, 'notes' => $notes, 'loan_application_id' => $application->id],
            );

            if ($decision === LoanApprovalDecision::REJECTED) {
                $application->update([
                    'status' => LoanApplicationStatus::REJECTED,
                    'rejection_reason' => $notes,
                    'decided_at' => now(),
                ]);

                $this->auditLogger->log(
                    event: 'loan_application.rejected',
                    actor: $approver,
                    subject: $application,
                    old: ['status' => LoanApplicationStatus::PENDING_REVIEW->value],
                    new: ['status' => LoanApplicationStatus::REJECTED->value, 'reason' => $notes],
                );

                return $application;
            }

            $approvedCount = $application->approvals()
                ->where('decision', LoanApprovalDecision::APPROVED)
                ->count();

            if ($approvedCount >= $tier->requiredApprovals) {
                $application->update([
                    'status' => LoanApplicationStatus::APPROVED,
                    'decided_at' => now(),
                ]);

                $this->disburse->execute($application);
            }

            return $application->fresh();
        });
    }
}
