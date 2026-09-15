<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Kyc;

use App\Actions\Members\RejectMemberKycAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Admin\Kyc\RejectKycRequest;
use App\Models\MemberKyc;
use Illuminate\Http\RedirectResponse;

class RejectKycController extends Controller
{
    public function __invoke(RejectKycRequest $request, MemberKyc $memberKyc, RejectMemberKycAction $action): RedirectResponse
    {
        $action->execute($memberKyc, $request->user(), $request->validated('reason'));

        return redirect()->route('admin.kyc.index')->with('status', 'KYC anggota ditolak.');
    }
}
