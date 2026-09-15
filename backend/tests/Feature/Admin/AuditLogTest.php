<?php

declare(strict_types=1);

use App\Enums\KycStatus;
use App\Enums\UserRole;
use App\Models\AuditLog;

it('records an audit entry when kyc is approved', function () {
    $member = memberWithSavingsAccounts();
    $member->kyc()->create([
        'ktp_photo_path' => 'x.jpg',
        'selfie_ktp_path' => 'y.jpg',
        'status' => KycStatus::PENDING,
    ]);
    $admin = adminUser();

    $this->actingAs($admin, 'web')->post("/admin/kyc/{$member->kyc->id}/approve");

    $log = AuditLog::where('event', 'member.kyc_approved')->first();
    expect($log)->not->toBeNull()
        ->and($log->user_id)->toBe($admin->id)
        ->and($log->auditable_id)->toBe($member->kyc->id);
});

it('allows an auditor to view the audit log', function () {
    memberWithSavingsAccounts();
    $auditor = adminUser(UserRole::AUDITOR);

    $response = $this->actingAs($auditor, 'web')->get('/admin/audit-logs');

    $response->assertOk();
});

it('forbids a treasurer from viewing the audit log', function () {
    $treasurer = adminUser(UserRole::TREASURER);

    $this->actingAs($treasurer, 'web')->get('/admin/audit-logs')->assertForbidden();
});

it('forbids a member from viewing the audit log', function () {
    $member = memberWithSavingsAccounts();

    $this->actingAs($member->user, 'web')->get('/admin/audit-logs')->assertForbidden();
});
