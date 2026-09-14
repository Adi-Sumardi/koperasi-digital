<?php

declare(strict_types=1);

it('lists the three savings accounts with zero balance for a new member', function () {
    $member = memberWithSavingsAccounts();

    $response = $this->actingAs($member->user, 'sanctum')->getJson('/api/v1/savings');

    $response->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonFragment(['type' => 'pokok', 'balance' => '0.00'])
        ->assertJsonFragment(['type' => 'wajib', 'balance' => '0.00'])
        ->assertJsonFragment(['type' => 'sukarela', 'balance' => '0.00']);
});

it('rejects unauthenticated access to savings summary', function () {
    $this->getJson('/api/v1/savings')->assertUnauthorized();
});
