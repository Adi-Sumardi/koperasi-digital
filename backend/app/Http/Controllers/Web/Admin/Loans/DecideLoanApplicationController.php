<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Loans;

use App\Actions\Loans\DecideLoanApplicationAction;
use App\Enums\LoanApprovalDecision;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Admin\Loans\DecideLoanApplicationRequest;
use App\Models\LoanApplication;
use Illuminate\Http\RedirectResponse;

class DecideLoanApplicationController extends Controller
{
    public function __invoke(
        DecideLoanApplicationRequest $request,
        LoanApplication $loanApplication,
        DecideLoanApplicationAction $action,
    ): RedirectResponse {
        $application = $action->execute(
            application: $loanApplication,
            approver: $request->user(),
            decision: LoanApprovalDecision::from($request->validated('decision')),
            notes: $request->validated('notes'),
        );

        $message = match ($application->status->value) {
            'approved' => 'Pengajuan disetujui dan pinjaman berhasil dicairkan.',
            'rejected' => 'Pengajuan ditolak.',
            default => 'Keputusan berhasil dicatat. Menunggu persetujuan jenjang berikutnya.',
        };

        return redirect()->route('admin.loans.applications.index')->with('status', $message);
    }
}
