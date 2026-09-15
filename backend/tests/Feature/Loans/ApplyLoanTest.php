<?php

declare(strict_types=1);

use App\Models\LoanProduct;

function applyForLoan($member, array $overrides = [], string $idempotencyKey = 'dddd1111-1111-1111-1111-111111111111')
{
    $product = LoanProduct::where('interest_type', 'flat')->firstOrFail();

    $payload = array_merge([
        'loan_product_id' => $product->id,
        'amount' => 5_000_000,
        'tenor_months' => 12,
        'purpose' => 'Kebutuhan mendesak',
    ], $overrides);

    return test()->actingAs($member->user, 'sanctum')->postJson('/api/v1/loans/apply', $payload, [
        'X-Idempotency-Key' => $idempotencyKey,
    ]);
}

it('submits a loan application within plafon and dsr limits', function () {
    $member = activeMemberWithSavings(totalSavingsSukarela: 5_000_000, monthlySalary: 10_000_000);

    $response = applyForLoan($member);

    $response->assertCreated()->assertJsonPath('data.status', 'pending_review');
});

it('rejects an application exceeding the 3x savings ceiling', function () {
    $member = activeMemberWithSavings(totalSavingsSukarela: 1_000_000, monthlySalary: 10_000_000);

    // total simpanan anggota ini kira-kira 1jt (sukarela) + 0 (pokok/wajib) = plafon ~3jt
    $response = applyForLoan($member, ['amount' => 50_000_000]);

    $response->assertStatus(400)->assertJsonPath('code', 'LOAN_WORKFLOW_VIOLATION');
});

it('rejects an application whose installment exceeds 35% of monthly salary (dsr)', function () {
    $member = activeMemberWithSavings(totalSavingsSukarela: 50_000_000, monthlySalary: 1_000_000);

    $response = applyForLoan($member, ['amount' => 20_000_000, 'tenor_months' => 12]);

    $response->assertStatus(400)->assertJsonPath('code', 'LOAN_WORKFLOW_VIOLATION');
});

it('rejects an application from a member who is not yet active', function () {
    $member = memberWithSavingsAccounts();

    $response = applyForLoan($member);

    $response->assertForbidden();
});

it('rejects an application without an idempotency key', function () {
    $member = activeMemberWithSavings();
    $product = LoanProduct::where('interest_type', 'flat')->firstOrFail();

    $response = test()->actingAs($member->user, 'sanctum')->postJson('/api/v1/loans/apply', [
        'loan_product_id' => $product->id,
        'amount' => 1_000_000,
        'tenor_months' => 6,
        'purpose' => 'Test',
    ]);

    $response->assertStatus(400)->assertJsonPath('code', 'IDEMPOTENCY_KEY_REQUIRED');
});
