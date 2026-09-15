<?php

declare(strict_types=1);

use App\Enums\KycStatus;
use App\Enums\MemberStatus;
use App\Enums\SavingsType;

function pendingKycMember()
{
    $member = memberWithSavingsAccounts();

    $member->kyc()->create([
        'ktp_photo_path' => 'kyc/test/ktp.jpg',
        'selfie_ktp_path' => 'kyc/test/selfie.jpg',
        'status' => KycStatus::PENDING,
    ]);

    return $member->refresh();
}

it('lists pending kyc submissions', function () {
    $member = pendingKycMember();

    $response = $this->actingAs(adminUser(), 'web')->get('/admin/kyc');

    $response->assertOk()->assertSee($member->full_name);
});

it('approves a kyc submission', function () {
    $member = pendingKycMember();
    $admin = adminUser();

    $response = $this->actingAs($admin, 'web')->post("/admin/kyc/{$member->kyc->id}/approve");

    $response->assertRedirect(route('admin.kyc.index'));

    $kyc = $member->kyc->fresh();
    expect($kyc->status)->toBe(KycStatus::APPROVED)
        ->and($kyc->verified_by)->toBe($admin->id);
});

it('activates the membership when kyc is approved after pokok is already fully paid', function () {
    $member = pendingKycMember();

    $pokok = $member->savingsAccounts()->where('type', SavingsType::POKOK)->first();
    $pokok->update(['balance' => config('koperasi.simpanan_pokok_amount')]);

    $this->actingAs(adminUser(), 'web')->post("/admin/kyc/{$member->kyc->id}/approve");

    expect($member->fresh()->status)->toBe(MemberStatus::ACTIVE)
        ->and($member->fresh()->member_number)->not->toBeNull();
});

it('does not activate membership on kyc approval alone when pokok is unpaid', function () {
    $member = pendingKycMember();

    $this->actingAs(adminUser(), 'web')->post("/admin/kyc/{$member->kyc->id}/approve");

    expect($member->fresh()->status)->toBe(MemberStatus::PENDING);
});

it('rejects a kyc submission with a reason', function () {
    $member = pendingKycMember();

    $response = $this->actingAs(adminUser(), 'web')->post("/admin/kyc/{$member->kyc->id}/reject", [
        'reason' => 'Foto KTP buram',
    ]);

    $response->assertRedirect(route('admin.kyc.index'));

    $kyc = $member->kyc->fresh();
    expect($kyc->status)->toBe(KycStatus::REJECTED)
        ->and($kyc->rejection_reason)->toBe('Foto KTP buram');
});

it('requires a reason to reject a kyc submission', function () {
    $member = pendingKycMember();

    $response = $this->actingAs(adminUser(), 'web')->post("/admin/kyc/{$member->kyc->id}/reject", []);

    $response->assertSessionHasErrors('reason');
});

it('forbids a member from accessing kyc review', function () {
    $member = memberWithSavingsAccounts();

    $this->actingAs($member->user, 'web')->get('/admin/kyc')->assertForbidden();
});
