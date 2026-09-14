<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class LoginController extends Controller
{
    public function __invoke(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        /** @var User|null $user */
        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Auth::validate([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ])) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, 'Email atau kata sandi salah.');
        }

        if (! $user->is_active) {
            throw new HttpException(Response::HTTP_FORBIDDEN, 'Akun Anda tidak aktif. Hubungi pengurus koperasi.');
        }

        $token = $user->createToken($credentials['device_name'])->plainTextToken;

        return ApiResponse::success(
            data: [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
            ],
            message: 'Login berhasil.',
        );
    }
}
