<?php

declare(strict_types=1);

use App\Enums\MemberStatus;

it('lists members', function () {
    $member = memberWithSavingsAccounts();

    $response = $this->actingAs(adminUser(), 'web')->get('/admin/members');

    $response->assertOk()->assertSee($member->full_name);
});

it('filters members by search term', function () {
    $match = memberWithSavingsAccounts();
    $other = memberWithSavingsAccounts();

    $response = $this->actingAs(adminUser(), 'web')
        ->get('/admin/members?search='.urlencode($match->employee_nip));

    $response->assertOk()
        ->assertSee($match->full_name)
        ->assertDontSee($other->full_name);
});

it('filters members by status', function () {
    $active = activeMemberWithSavings();
    $pending = memberWithSavingsAccounts();

    $response = $this->actingAs(adminUser(), 'web')->get('/admin/members?status=active');

    $response->assertOk()
        ->assertSee($active->full_name)
        ->assertDontSee($pending->full_name);
});

it('shows member detail with savings, loans, and kyc status', function () {
    $member = activeMemberWithSavings();

    $response = $this->actingAs(adminUser(), 'web')->get("/admin/members/{$member->id}");

    $response->assertOk()
        ->assertSee($member->full_name)
        ->assertSee($member->maskedNik())
        ->assertSee(MemberStatus::ACTIVE->label());
});

it('forbids a member from accessing member management', function () {
    $member = memberWithSavingsAccounts();

    $this->actingAs($member->user, 'web')->get('/admin/members')->assertForbidden();
});
