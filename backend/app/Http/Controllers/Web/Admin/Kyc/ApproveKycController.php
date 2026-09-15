<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Kyc;

use App\Actions\Members\ApproveMemberKycAction;
use App\Http\Controllers\Controller;
use App\Models\MemberKyc;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ApproveKycController extends Controller
{
    public function __invoke(Request $request, MemberKyc $memberKyc, ApproveMemberKycAction $action): RedirectResponse
    {
        $action->execute($memberKyc, $request->user());

        return redirect()->route('admin.kyc.index')->with('status', 'KYC anggota berhasil disetujui.');
    }
}
