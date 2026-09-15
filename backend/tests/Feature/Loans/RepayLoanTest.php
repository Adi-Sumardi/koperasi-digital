<?php

declare(strict_types=1);

use App\Enums\LoanStatus;
use App\Enums\UserRole;
use App\Models\JournalEntry;
use App\Models\LoanApplication;
use App\Models\LoanProduct;
use App\Models\User;
use Illuminate\Support\Str;

function disbursedLoan($member, float $amount = 3_000_000, int $tenorMonths = 3)
{
    $product = LoanProduct::where('interest_type', 'flat')->firstOrFail();

    $applyResponse = test()->actingAs($member->user, 'sanctum')->postJson('/api/v1/loans/apply', [
        'loan_product_id' => $product->id,
        'amount' => $amount,
        'tenor_months' => $tenorMonths,
        'purpose' => 'Test pelunasan',
    ], ['X-Idempotency-Key' => (string) Str::uuid()]);

    $application = LoanApplication::findOrFail($applyResponse->json('data.id'));

    $treasurer = User::factory()->create(['role' => UserRole::TREASURER]);
    test()->actingAs($treasurer, 'web')
        ->post("/admin/loans/applications/{$application->id}/decide", ['decision' => 'approved'])
        ->assertRedirect();

    return $member->loans()->firstOrFail();
}

function repay($member, $loan, string $idempotencyKey = 'eeee1111-1111-1111-1111-111111111111')
{
    return test()->actingAs($member->user, 'sanctum')->postJson("/api/v1/loans/{$loan->id}/repay", [], [
        'X-Idempotency-Key' => $idempotencyKey,
    ]);
}

it('repays the next unpaid installment and posts a balanced journal entry', function () {
    $member = activeMemberWithSavings(totalSavingsSukarela: 5_000_000, monthlySalary: 10_000_000);
    $loan = disbursedLoan($member);

    $response = repay($member, $loan);

    $response->assertOk()
        ->assertJsonPath('data.installment_number', 1)
        ->assertJsonPath('data.status', 'paid');

    expect((float) $loan->refresh()->outstanding_balance)->toBeLessThan((float) $loan->principal_amount);

    $entry = JournalEntry::where('reference_type', 'loan_installment')->latest('created_at')->first();
    expect((float) $entry->lines->sum('debit'))->toBe((float) $entry->lines->sum('credit'));
});

it('marks the loan as paid off once every installment is paid', function () {
    $member = activeMemberWithSavings(totalSavingsSukarela: 5_000_000, monthlySalary: 10_000_000);
    $loan = disbursedLoan($member, amount: 3_000_000, tenorMonths: 3);

    repay($member, $loan, 'eeee2222-2222-2222-2222-222222222222')->assertOk();
    repay($member, $loan, 'eeee3333-3333-3333-3333-333333333333')->assertOk();
    repay($member, $loan, 'eeee4444-4444-4444-4444-444444444444')->assertOk();

    expect($loan->refresh()->status)->toBe(LoanStatus::PAID_OFF)
        ->and((float) $loan->outstanding_balance)->toBe(0.0);
});

it('rejects repayment without an idempotency key', function () {
    $member = activeMemberWithSavings(totalSavingsSukarela: 5_000_000, monthlySalary: 10_000_000);
    $loan = disbursedLoan($member);

    $response = test()->actingAs($member->user, 'sanctum')->postJson("/api/v1/loans/{$loan->id}/repay");

    $response->assertStatus(400)->assertJsonPath('code', 'IDEMPOTENCY_KEY_REQUIRED');
});

it('forbids repaying a loan that does not belong to the member', function () {
    $owner = activeMemberWithSavings(totalSavingsSukarela: 5_000_000, monthlySalary: 10_000_000);
    $loan = disbursedLoan($owner);

    $intruder = activeMemberWithSavings(totalSavingsSukarela: 5_000_000, monthlySalary: 10_000_000);

    $response = repay($intruder, $loan);

    $response->assertForbidden();
});
