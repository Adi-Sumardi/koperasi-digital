<?php

declare(strict_types=1);

use App\Http\Controllers\Web\Admin\Auth\LoginController;
use App\Http\Controllers\Web\Admin\DashboardController;
use App\Http\Controllers\Web\Admin\Kyc\ApproveKycController;
use App\Http\Controllers\Web\Admin\Kyc\KycReviewController;
use App\Http\Controllers\Web\Admin\Kyc\RejectKycController;
use App\Http\Controllers\Web\Admin\Loans\DecideLoanApplicationController;
use App\Http\Controllers\Web\Admin\Loans\LoanApplicationReviewController;
use App\Http\Controllers\Web\Admin\Members\MemberController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:web')->group(function () {
        Route::get('login', [LoginController::class, 'create'])->name('login');
        Route::post('login', [LoginController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth:web', 'admin.role'])->group(function () {
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

        Route::get('/', DashboardController::class)->name('dashboard');

        Route::prefix('kyc')->name('kyc.')->group(function () {
            Route::get('/', [KycReviewController::class, 'index'])->name('index');
            Route::get('{memberKyc}', [KycReviewController::class, 'show'])->name('show');
            Route::post('{memberKyc}/approve', ApproveKycController::class)->name('approve');
            Route::post('{memberKyc}/reject', RejectKycController::class)->name('reject');
        });

        Route::prefix('loans/applications')->name('loans.applications.')->group(function () {
            Route::get('/', [LoanApplicationReviewController::class, 'index'])->name('index');
            Route::get('{loanApplication}', [LoanApplicationReviewController::class, 'show'])->name('show');
            Route::post('{loanApplication}/decide', DecideLoanApplicationController::class)->name('decide');
        });

        Route::prefix('members')->name('members.')->group(function () {
            Route::get('/', [MemberController::class, 'index'])->name('index');
            Route::get('{member}', [MemberController::class, 'show'])->name('show');
        });
    });
});
