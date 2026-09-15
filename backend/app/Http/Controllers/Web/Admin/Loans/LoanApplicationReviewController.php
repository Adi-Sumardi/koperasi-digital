<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Loans;

use App\Enums\LoanApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\LoanApplication;
use App\Services\Loans\LoanApprovalTierService;
use Illuminate\Contracts\View\View;

class LoanApplicationReviewController extends Controller
{
    public function index(): View
    {
        $applications = LoanApplication::with('member', 'loanProduct')
            ->where('status', LoanApplicationStatus::PENDING_REVIEW)
            ->latest('created_at')
            ->paginate(20);

        return view('admin.loans.index', ['applications' => $applications]);
    }

    public function show(LoanApplication $loanApplication, LoanApprovalTierService $tierService): View
    {
        $loanApplication->load('member.user', 'loanProduct', 'approvals.approver');

        return view('admin.loans.show', [
            'application' => $loanApplication,
            'tier' => $tierService->resolve((float) $loanApplication->amount),
        ]);
    }
}
