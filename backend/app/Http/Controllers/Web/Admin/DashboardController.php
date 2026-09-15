<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Enums\KycStatus;
use App\Enums\LoanApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\LoanApplication;
use App\Models\MemberKyc;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('admin.dashboard', [
            'pendingKycCount' => MemberKyc::where('status', KycStatus::PENDING)->count(),
            'pendingLoanCount' => LoanApplication::where('status', LoanApplicationStatus::PENDING_REVIEW)->count(),
            'undecidedByMeCount' => LoanApplication::where('status', LoanApplicationStatus::PENDING_REVIEW)
                ->whereDoesntHave('approvals', fn ($query) => $query->where('approver_id', $request->user()->id))
                ->count(),
        ]);
    }
}
