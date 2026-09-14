<?php

declare(strict_types=1);

use App\Models\JournalEntry;

function withdrawAs($member, array $payload, string $idempotencyKey = '99999999-9999-9999-9999-999999999999')
{
    return test()->actingAs($member->user, 'sanctum')->postJson('/api/v1/savings/withdraw', $payload, [
        'X-Idempotency-Key' => $idempotencyKey,
    ]);
}

it('withdraws from the sukarela account and posts a balanced journal entry', function () {
    $member = memberWithSavingsAccounts();
    $account = $member->savingsAccounts()->where('type', 'sukarela')->first();
    $account->update(['balance' => 100000]);

    $response = withdrawAs($member, ['amount' => 40000]);

    $response->assertOk()
        ->assertJsonPath('data.type', 'withdrawal')
        ->assertJsonPath('data.balance_after', '60000.00');

    expect((float) $account->refresh()->balance)->toBe(60000.0);

    $entry = JournalEntry::with('lines')->latest('created_at')->first();
    expect((float) $entry->lines->sum('debit'))->toBe((float) $entry->lines->sum('credit'))
        ->and((float) $entry->lines->sum('debit'))->toBe(40000.0);
});

it('rejects a withdrawal larger than the available balance', function () {
    $member = memberWithSavingsAccounts();
    $account = $member->savingsAccounts()->where('type', 'sukarela')->first();
    $account->update(['balance' => 10000]);

    $response = withdrawAs($member, ['amount' => 50000]);

    $response->assertStatus(400)->assertJsonPath('code', 'INSUFFICIENT_BALANCE');

    expect((float) $account->refresh()->balance)->toBe(10000.0);
});

it('rejects a withdrawal without an idempotency key', function () {
    $member = memberWithSavingsAccounts();

    $response = test()->actingAs($member->user, 'sanctum')
        ->postJson('/api/v1/savings/withdraw', ['amount' => 10000]);

    $response->assertStatus(400)->assertJsonPath('code', 'IDEMPOTENCY_KEY_REQUIRED');
});
