<?php

declare(strict_types=1);

use App\Http\Controllers\Web\Admin\Audit\AuditLogController;
use App\Http\Controllers\Web\Admin\Auth\LoginController;
use App\Http\Controllers\Web\Admin\Auth\TwoFactorChallengeController;
use App\Http\Controllers\Web\Admin\Auth\TwoFactorSetupController;
use App\Http\Controllers\Web\Admin\DashboardController;
use App\Http\Controllers\Web\Admin\Kyc\ApproveKycController;
use App\Http\Controllers\Web\Admin\Kyc\KycReviewController;
use App\Http\Controllers\Web\Admin\Kyc\RejectKycController;
use App\Http\Controllers\Web\Admin\Loans\DecideLoanApplicationController;
use App\Http\Controllers\Web\Admin\Loans\LoanApplicationReviewController;
use App\Http\Controllers\Web\Admin\Members\MemberController;
use App\Http\Controllers\Web\Admin\Settings\SecuritySettingsController;
use App\Http\Controllers\Web\Admin\Settings\TwoFactorSettingsController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:web')->group(function () {
        Route::get('login', [LoginController::class, 'create'])->name('login');
        Route::post('login', [LoginController::class, 'store'])->middleware('throttle:6,1')->name('login.store');

        Route::prefix('2fa')->name('2fa.')->group(function () {
            Route::get('setup', [TwoFactorSetupController::class, 'create'])->name('setup');
            Route::post('setup', [TwoFactorSetupController::class, 'store'])->middleware('throttle:6,1')->name('setup.store');
            Route::get('challenge', [TwoFactorChallengeController::class, 'create'])->name('challenge');
            Route::post('challenge', [TwoFactorChallengeController::class, 'store'])->middleware('throttle:6,1')->name('challenge.store');
        });
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

        Route::middleware('admin.role:auditor,superadmin')
            ->get('audit-logs', [AuditLogController::class, 'index'])
            ->name('audit-logs.index');

        Route::prefix('settings/security')->name('settings.security.')->group(function () {
            Route::get('/', [SecuritySettingsController::class, 'index'])->name('index');
            Route::get('2fa/enable', [TwoFactorSettingsController::class, 'create'])->name('2fa.enable');
            Route::post('2fa/enable', [TwoFactorSettingsController::class, 'store'])->name('2fa.enable.store');
            Route::post('2fa/disable', [TwoFactorSettingsController::class, 'destroy'])->name('2fa.disable');
        });
    });
});
