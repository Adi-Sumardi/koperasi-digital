<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\MeController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\Auth\SubmitKycController;
use App\Http\Controllers\Api\V1\Loans\ApplyLoanController;
use App\Http\Controllers\Api\V1\Loans\LoanIndexController;
use App\Http\Controllers\Api\V1\Loans\RepayLoanController;
use App\Http\Controllers\Api\V1\Loans\SimulateLoanController;
use App\Http\Controllers\Api\V1\Savings\DepositSavingsController;
use App\Http\Controllers\Api\V1\Savings\SavingsSummaryController;
use App\Http\Controllers\Api\V1\Savings\WithdrawSavingsController;
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

    Route::middleware('auth:sanctum')->prefix('savings')->name('savings.')->group(function () {
        Route::get('/', SavingsSummaryController::class)->name('index');

        Route::middleware('idempotency')->group(function () {
            Route::post('deposit', DepositSavingsController::class)->name('deposit');
            Route::post('withdraw', WithdrawSavingsController::class)->name('withdraw');
        });
    });

    Route::middleware('auth:sanctum')->prefix('loans')->name('loans.')->group(function () {
        Route::get('simulate', SimulateLoanController::class)->name('simulate');
        Route::get('/', LoanIndexController::class)->name('index');

        Route::middleware('idempotency')->group(function () {
            Route::post('apply', ApplyLoanController::class)->name('apply');
            Route::post('{loan}/repay', RepayLoanController::class)->name('repay');
        });
    });
});
