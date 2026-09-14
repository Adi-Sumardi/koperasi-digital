<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\MeController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\Auth\SubmitKycController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('register', RegisterController::class)->name('register');
        Route::post('login', LoginController::class)->name('login');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', LogoutController::class)->name('logout');
            Route::get('me', MeController::class)->name('me');
            Route::post('kyc/submit', SubmitKycController::class)->name('kyc.submit');
        });
    });
});
