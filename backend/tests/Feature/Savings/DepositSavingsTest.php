<?php

declare(strict_types=1);

use App\Enums\KycStatus;
use App\Enums\MemberStatus;
use App\Models\JournalEntry;

function depositAs($member, array $payload, string $idempotencyKey = '11111111-1111-1111-1111-111111111111')
{
    return test()->actingAs($member->user, 'sanctum')->postJson('/api/v1/savings/deposit', $payload, [
        'X-Idempotency-Key' => $idempotencyKey,
    ]);
}

it('rejects a deposit without an idempotency key', function () {
    $member = memberWithSavingsAccounts();

    $response = test()->actingAs($member->user, 'sanctum')
        ->postJson('/api/v1/savings/deposit', ['type' => 'sukarela', 'amount' => 10000]);

    $response->assertStatus(400)->assertJsonPath('code', 'IDEMPOTENCY_KEY_REQUIRED');
});

it('deposits into a savings account, increments balance, and posts a balanced journal entry', function () {
    $member = memberWithSavingsAccounts();

    $response = depositAs($member, ['type' => 'sukarela', 'amount' => 50000]);

    $response->assertCreated()
        ->assertJsonPath('data.type', 'deposit')
        ->assertJsonPath('data.balance_after', '50000.00');

    $account = $member->savingsAccounts()->where('type', 'sukarela')->first();
    expect((float) $account->balance)->toBe(50000.0);

    $entry = JournalEntry::with('lines')->latest('created_at')->first();
    expect($entry->lines)->toHaveCount(2);
    expect((float) $entry->lines->sum('debit'))->toBe((float) $entry->lines->sum('credit'));
});

it('returns the cached response for a repeated idempotency key instead of double-crediting', function () {
    $member = memberWithSavingsAccounts();

    $first = depositAs($member, ['type' => 'sukarela', 'amount' => 50000]);
    $second = depositAs($member, ['type' => 'sukarela', 'amount' => 50000]);

    $first->assertCreated();
    $second->assertCreated();
    expect($second->json('data.id'))->toBe($first->json('data.id'));

    $account = $member->savingsAccounts()->where('type', 'sukarela')->first();
    expect((float) $account->balance)->toBe(50000.0);
});

it('does not activate membership when pokok is fully paid but kyc is not approved', function () {
    $member = memberWithSavingsAccounts();

    depositAs($member, ['type' => 'pokok', 'amount' => 1_000_000])->assertCreated();

    expect($member->refresh()->status)->toBe(MemberStatus::PENDING)
        ->and($member->member_number)->toBeNull();
});

it('activates membership and issues a member number once kyc is approved and pokok is fully paid', function () {
    $member = memberWithSavingsAccounts();
    $member->kyc()->create([
        'ktp_photo_path' => 'kyc/test/ktp.jpg',
        'selfie_ktp_path' => 'kyc/test/selfie.jpg',
        'status' => KycStatus::APPROVED,
        'verified_at' => now(),
    ]);

    depositAs($member, ['type' => 'pokok', 'amount' => 1_000_000])->assertCreated();

    $member->refresh();
    expect($member->status)->toBe(MemberStatus::ACTIVE)
        ->and($member->member_number)->toStartWith('KOP-'.now()->year.'-')
        ->and($member->joined_at)->not->toBeNull();
});

it('rejects an invalid savings type', function () {
    $member = memberWithSavingsAccounts();

    $response = depositAs($member, ['type' => 'invalid', 'amount' => 10000]);

    $response->assertUnprocessable()->assertJsonValidationErrors('type');
});
