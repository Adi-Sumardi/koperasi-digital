<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin\Kyc;

use App\Enums\KycStatus;
use App\Http\Controllers\Controller;
use App\Models\MemberKyc;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;

class KycReviewController extends Controller
{
    public function index(): View
    {
        $submissions = MemberKyc::with('member')
            ->where('status', KycStatus::PENDING)
            ->latest('created_at')
            ->paginate(20);

        return view('admin.kyc.index', ['submissions' => $submissions]);
    }

    public function show(MemberKyc $memberKyc): View
    {
        $memberKyc->load('member.user', 'verifier');

        return view('admin.kyc.show', [
            'kyc' => $memberKyc,
            // Presigned URL berlaku maksimal 5 menit (rules/security.md §2).
            'ktpPhotoUrl' => Storage::disk('s3')->temporaryUrl($memberKyc->ktp_photo_path, now()->addMinutes(5)),
            'selfiePhotoUrl' => Storage::disk('s3')->temporaryUrl($memberKyc->selfie_ktp_path, now()->addMinutes(5)),
        ]);
    }
}
