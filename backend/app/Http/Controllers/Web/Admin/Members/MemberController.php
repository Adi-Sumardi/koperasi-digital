<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Members;

use App\Enums\MemberStatus;
use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function index(Request $request): View
    {
        $members = Member::query()
            ->with('user')
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = "%{$request->string('search')}%";
                $query->where(fn ($q) => $q
                    ->where('full_name', 'ilike', $term)
                    ->orWhere('employee_nip', 'ilike', $term)
                    ->orWhere('member_number', 'ilike', $term));
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.members.index', [
            'members' => $members,
            'statuses' => MemberStatus::cases(),
        ]);
    }

    public function show(Member $member): View
    {
        $member->load(['user', 'kyc.verifier', 'savingsAccounts', 'loans.installments', 'loanApplications.loanProduct']);

        return view('admin.members.show', ['member' => $member]);
    }
}
