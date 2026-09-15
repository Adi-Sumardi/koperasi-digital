<?php

declare(strict_types=1);

use App\Enums\LoanApplicationStatus;
use App\Enums\UserRole;
use App\Models\LoanApplication;
use App\Models\LoanProduct;
use App\Models\User;
use Illuminate\Support\Str;

function submitApplication($member, float $amount, int $tenorMonths = 12): LoanApplication
{
    $product = LoanProduct::where('interest_type', 'flat')->firstOrFail();

    $response = test()->actingAs($member->user, 'sanctum')->postJson('/api/v1/loans/apply', [
        'loan_product_id' => $product->id,
        'amount' => $amount,
        'tenor_months' => $tenorMonths,
        'purpose' => 'Test pengajuan',
    ], ['X-Idempotency-Key' => (string) Str::uuid()]);

    $response->assertCreated();

    return LoanApplication::findOrFail($response->json('data.id'));
}

it('disburses the loan once the tier-1 single approval is met', function () {
    $member = activeMemberWithSavings(totalSavingsSukarela: 5_000_000, monthlySalary: 10_000_000);
    $application = submitApplication($member, 2_000_000, 6);

    $treasurer = User::factory()->create(['role' => UserRole::TREASURER]);

    $response = test()->actingAs($treasurer, 'sanctum')
        ->postJson("/api/v1/loans/applications/{$application->id}/decide", ['decision' => 'approved']);

    $response->assertOk()->assertJsonPath('data.status', 'approved');

    expect($member->loans()->count())->toBe(1);
    expect($member->loans()->first()->installments()->count())->toBe(6);
});

it('requires two approvals before disbursing a tier-2 loan', function () {
    $member = activeMemberWithSavings(totalSavingsSukarela: 10_000_000, monthlySalary: 10_000_000);
    $application = submitApplication($member, 12_000_000, 12);

    $treasurer = User::factory()->create(['role' => UserRole::TREASURER]);
    $chairman = User::factory()->create(['role' => UserRole::CHAIRMAN]);

    test()->actingAs($treasurer, 'sanctum')
        ->postJson("/api/v1/loans/applications/{$application->id}/decide", ['decision' => 'approved'])
        ->assertOk()
        ->assertJsonPath('data.status', 'pending_review');

    expect($member->loans()->count())->toBe(0);

    test()->actingAs($chairman, 'sanctum')
        ->postJson("/api/v1/loans/applications/{$application->id}/decide", ['decision' => 'approved'])
        ->assertOk()
        ->assertJsonPath('data.status', 'approved');

    expect($member->loans()->count())->toBe(1);
});

it('immediately rejects the application on a single rejection', function () {
    $member = activeMemberWithSavings(totalSavingsSukarela: 5_000_000, monthlySalary: 10_000_000);
    $application = submitApplication($member, 2_000_000, 6);

    $treasurer = User::factory()->create(['role' => UserRole::TREASURER]);

    test()->actingAs($treasurer, 'sanctum')
        ->postJson("/api/v1/loans/applications/{$application->id}/decide", [
            'decision' => 'rejected',
            'notes' => 'Tidak memenuhi syarat',
        ])
        ->assertOk()
        ->assertJsonPath('data.status', 'rejected');

    expect($application->fresh()->status)->toBe(LoanApplicationStatus::REJECTED);
});

it('forbids a member from deciding their own application', function () {
    $member = activeMemberWithSavings(totalSavingsSukarela: 5_000_000, monthlySalary: 10_000_000);
    $application = submitApplication($member, 2_000_000, 6);

    test()->actingAs($member->user, 'sanctum')
        ->postJson("/api/v1/loans/applications/{$application->id}/decide", ['decision' => 'approved'])
        ->assertForbidden();
});

it('rejects a second decision from the same approver', function () {
    $member = activeMemberWithSavings(totalSavingsSukarela: 10_000_000, monthlySalary: 10_000_000);
    $application = submitApplication($member, 12_000_000, 12);

    $treasurer = User::factory()->create(['role' => UserRole::TREASURER]);

    test()->actingAs($treasurer, 'sanctum')
        ->postJson("/api/v1/loans/applications/{$application->id}/decide", ['decision' => 'approved'])
        ->assertOk();

    test()->actingAs($treasurer, 'sanctum')
        ->postJson("/api/v1/loans/applications/{$application->id}/decide", ['decision' => 'approved'])
        ->assertStatus(400)
        ->assertJsonPath('code', 'LOAN_WORKFLOW_VIOLATION');
});
