<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\RegisterMemberAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterMemberRequest;
use App\Http\Resources\MemberResource;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class RegisterController extends Controller
{
    public function __invoke(RegisterMemberRequest $request, RegisterMemberAction $action): JsonResponse
    {
        $member = $action->execute($request->validated());
        $token = $member->user->createToken('mobile')->plainTextToken;

        return ApiResponse::success(
            data: [
                'member' => new MemberResource($member),
                'token' => $token,
            ],
            message: 'Registrasi anggota berhasil. Silakan lanjutkan verifikasi KYC.',
            code: Response::HTTP_CREATED,
        );
    }
}
