<?php

declare(strict_types=1);

use App\Enums\MemberStatus;
use App\Models\Member;
use App\Models\User;

function registrationPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Budi Santoso',
        'email' => 'budi@example.com',
        'phone_number' => '+6281234567890',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'nik' => '3201042004050001',
        'employee_nip' => 'NIP-0001',
        'department' => 'IT',
        'bank_name' => 'BCA',
        'bank_account_number' => '1234567890',
        'monthly_salary' => 8000000,
    ], $overrides);
}

it('registers a new member and issues a sanctum token', function () {
    $response = $this->postJson('/api/v1/auth/register', registrationPayload());

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.member.full_name', 'Budi Santoso')
        ->assertJsonPath('data.member.status', 'pending')
        ->assertJsonStructure(['data' => ['token']]);

    expect(User::where('email', 'budi@example.com')->exists())->toBeTrue();

    $member = Member::first();
    expect($member->nik)->toBe('3201042004050001')
        ->and($member->status)->toBe(MemberStatus::PENDING)
        ->and($member->member_number)->toBeNull();
});

it('rejects registration with a duplicate nik', function () {
    $this->postJson('/api/v1/auth/register', registrationPayload())->assertCreated();

    $response = $this->postJson('/api/v1/auth/register', registrationPayload([
        'email' => 'lain@example.com',
        'phone_number' => '+6281234567891',
        'employee_nip' => 'NIP-0002',
    ]));

    $response->assertUnprocessable()->assertJsonValidationErrors('nik');
});

it('rejects registration with an invalid nik format', function () {
    $response = $this->postJson('/api/v1/auth/register', registrationPayload(['nik' => '12345']));

    $response->assertUnprocessable()->assertJsonValidationErrors('nik');
});

it('rejects registration with a duplicate email', function () {
    $this->postJson('/api/v1/auth/register', registrationPayload())->assertCreated();

    $response = $this->postJson('/api/v1/auth/register', registrationPayload([
        'phone_number' => '+6281234567899',
        'nik' => '3201042004050099',
        'employee_nip' => 'NIP-0099',
    ]));

    $response->assertUnprocessable()->assertJsonValidationErrors('email');
});
