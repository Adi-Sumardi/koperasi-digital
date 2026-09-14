<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\SubmitKycAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SubmitKycRequest;
use App\Http\Resources\MemberKycResource;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class SubmitKycController extends Controller
{
    public function __invoke(SubmitKycRequest $request, SubmitKycAction $action): JsonResponse
    {
        $kyc = $action->execute(
            member: $request->user()->member,
            ktpPhoto: $request->file('ktp_photo'),
            selfieKtp: $request->file('selfie_ktp'),
        );

        return ApiResponse::success(
            data: new MemberKycResource($kyc),
            message: 'Berkas KYC berhasil diunggah dan menunggu verifikasi.',
        );
    }
}
