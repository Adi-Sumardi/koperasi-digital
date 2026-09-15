<?php

declare(strict_types=1);

use App\Actions\Loans\ApplyLoanAction;
use App\Enums\LoanApplicationStatus;
use App\Enums\UserRole;
use App\Models\LoanApplication;
use App\Models\LoanProduct;
use PragmaRX\Google2FA\Google2FA;

function pendingApplication(float $amount = 2_000_000, int $tenorMonths = 6): LoanApplication
{
    $member = activeMemberWithSavings(totalSavingsSukarela: 10_000_000, monthlySalary: 10_000_000);
    $product = LoanProduct::where('interest_type', 'flat')->firstOrFail();

    return app(ApplyLoanAction::class)->execute(
        member: $member,
        product: $product,
        amount: $amount,
        tenorMonths: $tenorMonths,
        purpose: 'Test',
        guaranteeType: null,
    );
}

it('lists pending loan applications', function () {
    $application = pendingApplication();

    $response = $this->actingAs(adminUser(), 'web')->get('/admin/loans/applications');

    $response->assertOk()->assertSee($application->application_number);
});

it('disburses the loan once a tier-1 application is approved', function () {
    $application = pendingApplication(2_000_000, 6);
    $treasurer = adminUser(UserRole::TREASURER);

    $response = $this->actingAs($treasurer, 'web')
        ->post("/admin/loans/applications/{$application->id}/decide", ['decision' => 'approved']);

    $response->assertRedirect(route('admin.loans.applications.index'));

    $application->refresh();
    expect($application->status)->toBe(LoanApplicationStatus::APPROVED)
        ->and($application->loan)->not->toBeNull()
        ->and($application->loan->installments()->count())->toBe(6);
});

it('requires two approvals before disbursing a tier-2 loan', function () {
    $application = pendingApplication(12_000_000, 12);
    $treasurer = adminUser(UserRole::TREASURER);
    $chairman = adminUser(UserRole::CHAIRMAN);

    $this->actingAs($treasurer, 'web')
        ->post("/admin/loans/applications/{$application->id}/decide", ['decision' => 'approved'])
        ->assertRedirect();

    expect($application->refresh()->status)->toBe(LoanApplicationStatus::PENDING_REVIEW)
        ->and($application->loan)->toBeNull();

    $this->actingAs($chairman, 'web')
        ->post("/admin/loans/applications/{$application->id}/decide", ['decision' => 'approved'])
        ->assertRedirect();

    expect($application->refresh()->status)->toBe(LoanApplicationStatus::APPROVED)
        ->and($application->loan)->not->toBeNull();
});

it('rejects a second decision from the same approver', function () {
    $application = pendingApplication(12_000_000, 12);
    $treasurer = adminUser(UserRole::TREASURER);

    $this->actingAs($treasurer, 'web')
        ->post("/admin/loans/applications/{$application->id}/decide", ['decision' => 'approved'])
        ->assertRedirect();

    $response = $this->actingAs($treasurer, 'web')
        ->post("/admin/loans/applications/{$application->id}/decide", ['decision' => 'approved']);

    $response->assertSessionHasErrors('error');
    expect($application->approvals()->count())->toBe(1);
});

it('rejects a loan application and does not disburse', function () {
    $application = pendingApplication();
    $treasurer = adminUser(UserRole::TREASURER);

    $this->actingAs($treasurer, 'web')
        ->post("/admin/loans/applications/{$application->id}/decide", ['decision' => 'rejected', 'notes' => 'Tidak lengkap']);

    $application->refresh();
    expect($application->status)->toBe(LoanApplicationStatus::REJECTED)
        ->and($application->loan)->toBeNull();
});

it('flashes an error instead of crashing when the wrong role tries to decide a high-tier application', function () {
    $application = pendingApplication(30_000_000, 24);
    $treasurer = adminUser(UserRole::TREASURER); // tier >25jt requires chairman/superadmin only

    // 2FA valid disiapkan supaya penolakan yang diuji murni berbasis role, bukan syarat 2FA.
    $secret = enableTwoFactorFor($treasurer);
    $code = app(Google2FA::class)->getCurrentOtp($secret);

    $response = $this->actingAs($treasurer, 'web')
        ->post("/admin/loans/applications/{$application->id}/decide", ['decision' => 'approved', 'totp_code' => $code]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('error');

    expect($application->refresh()->status)->toBe(LoanApplicationStatus::PENDING_REVIEW);
});

it('requires a valid 2fa code to approve a high-tier loan application', function () {
    $application = pendingApplication(30_000_000, 24);
    $chairman = adminUser(UserRole::CHAIRMAN);

    $withoutCode = $this->actingAs($chairman, 'web')
        ->post("/admin/loans/applications/{$application->id}/decide", ['decision' => 'approved']);
    $withoutCode->assertSessionHasErrors('totp_code');
    expect($application->refresh()->status)->toBe(LoanApplicationStatus::PENDING_REVIEW);

    $secret = enableTwoFactorFor($chairman);

    $wrongCode = $this->actingAs($chairman, 'web')
        ->post("/admin/loans/applications/{$application->id}/decide", ['decision' => 'approved', 'totp_code' => '000000']);
    $wrongCode->assertSessionHasErrors('totp_code');
    expect($application->refresh()->status)->toBe(LoanApplicationStatus::PENDING_REVIEW);

    $code = app(Google2FA::class)->getCurrentOtp($secret);
    $success = $this->actingAs($chairman, 'web')
        ->post("/admin/loans/applications/{$application->id}/decide", ['decision' => 'approved', 'totp_code' => $code]);
    $success->assertRedirect()->assertSessionDoesntHaveErrors();

    // Tier >Rp25jt butuh 2 approval — satu approval chairman yang valid tercatat,
    // tapi pengajuan belum pindah status sampai jenjang terpenuhi (lihat test tier-2).
    expect($application->approvals()->where('approver_id', $chairman->id)->exists())->toBeTrue()
        ->and($application->refresh()->status)->toBe(LoanApplicationStatus::PENDING_REVIEW);
});

it('does not require a 2fa code to reject a high-tier loan application', function () {
    $application = pendingApplication(30_000_000, 24);
    $chairman = adminUser(UserRole::CHAIRMAN);

    $response = $this->actingAs($chairman, 'web')
        ->post("/admin/loans/applications/{$application->id}/decide", ['decision' => 'rejected', 'notes' => 'Tidak sesuai']);

    $response->assertRedirect();
    expect($application->refresh()->status)->toBe(LoanApplicationStatus::REJECTED);
});

it('forbids a member from accessing loan application review', function () {
    $member = memberWithSavingsAccounts();

    $this->actingAs($member->user, 'web')->get('/admin/loans/applications')->assertForbidden();
});
