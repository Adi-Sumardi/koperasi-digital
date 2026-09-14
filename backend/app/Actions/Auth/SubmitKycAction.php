<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Enums\KycStatus;
use App\Models\Member;
use App\Models\MemberKyc;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class SubmitKycAction
{
    public function execute(Member $member, UploadedFile $ktpPhoto, UploadedFile $selfieKtp): MemberKyc
    {
        return DB::transaction(function () use ($member, $ktpPhoto, $selfieKtp) {
            $ktpPath = $ktpPhoto->storeAs(
                "kyc/{$member->id}",
                'ktp.'.$ktpPhoto->extension(),
                's3'
            );

            $selfiePath = $selfieKtp->storeAs(
                "kyc/{$member->id}",
                'selfie.'.$selfieKtp->extension(),
                's3'
            );

            return MemberKyc::updateOrCreate(
                ['member_id' => $member->id],
                [
                    'ktp_photo_path' => $ktpPath,
                    'selfie_ktp_path' => $selfiePath,
                    'status' => KycStatus::PENDING,
                    'rejection_reason' => null,
                    'verified_by' => null,
                    'verified_at' => null,
                ]
            );
        });
    }
}
