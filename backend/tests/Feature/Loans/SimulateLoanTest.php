<?php

declare(strict_types=1);

use App\Models\LoanProduct;

it('simulates a flat interest loan', function () {
    $member = activeMemberWithSavings();
    $product = LoanProduct::where('interest_type', 'flat')->firstOrFail();

    $response = $this->actingAs($member->user, 'sanctum')
        ->getJson("/api/v1/loans/simulate?loan_product_id={$product->id}&amount=12000000&tenor_months=12");

    $response->assertOk()
        ->assertJsonPath('data.total_repayment', '13152000.00')
        ->assertJsonPath('data.monthly_installment', '1096000.00')
        ->assertJsonCount(12, 'data.schedule');
});

it('simulates a sliding interest loan with a declining interest portion', function () {
    $member = activeMemberWithSavings();
    $product = LoanProduct::where('interest_type', 'sliding')->firstOrFail();

    $response = $this->actingAs($member->user, 'sanctum')
        ->getJson("/api/v1/loans/simulate?loan_product_id={$product->id}&amount=12000000&tenor_months=12");

    $response->assertOk();
    $schedule = $response->json('data.schedule');

    expect((float) $schedule[0]['interest_portion'])->toBeGreaterThan((float) $schedule[1]['interest_portion']);
});

it('rejects a tenor outside the product range', function () {
    $member = activeMemberWithSavings();
    $product = LoanProduct::where('interest_type', 'flat')->firstOrFail();

    $response = $this->actingAs($member->user, 'sanctum')
        ->getJson("/api/v1/loans/simulate?loan_product_id={$product->id}&amount=1000000&tenor_months=999");

    $response->assertStatus(400)->assertJsonPath('code', 'LOAN_WORKFLOW_VIOLATION');
});
