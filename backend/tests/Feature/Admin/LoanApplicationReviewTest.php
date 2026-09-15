<?php

declare(strict_types=1);

use App\Actions\Loans\ApplyLoanAction;
use App\Enums\LoanApplicationStatus;
use App\Enums\UserRole;
use App\Models\LoanApplication;
use App\Models\LoanProduct;

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

    $response = $this->actingAs($treasurer, 'web')
        ->post("/admin/loans/applications/{$application->id}/decide", ['decision' => 'approved']);

    $response->assertRedirect();
    $response->assertSessionHasErrors('error');

    expect($application->refresh()->status)->toBe(LoanApplicationStatus::PENDING_REVIEW);
});

it('forbids a member from accessing loan application review', function () {
    $member = memberWithSavingsAccounts();

    $this->actingAs($member->user, 'web')->get('/admin/loans/applications')->assertForbidden();
});
