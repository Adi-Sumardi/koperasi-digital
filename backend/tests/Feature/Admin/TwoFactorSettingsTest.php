<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;
use PragmaRX\Google2FA\Google2FA;

function pengurus(): User
{
    return User::factory()->create(['role' => UserRole::TREASURER, 'password' => bcrypt('password123')]);
}

it('shows the security settings page with the current 2fa status', function () {
    $user = pengurus();

    $response = $this->actingAs($user, 'web')->get('/admin/settings/security');

    $response->assertOk()->assertSee('Belum Aktif');
});

it('lets a pengurus voluntarily enable 2fa', function () {
    $user = pengurus();

    $setup = $this->actingAs($user, 'web')->get('/admin/settings/security/2fa/enable');
    $setup->assertOk();

    $secret = session('settings_2fa_setup_secret');
    $code = app(Google2FA::class)->getCurrentOtp($secret);

    $response = $this->post('/admin/settings/security/2fa/enable', ['code' => $code]);

    $response->assertOk()->assertSee('Kode Pemulihan', false);
    expect($user->fresh()->hasEnabledTwoFactor())->toBeTrue();
});

it('rejects an invalid code when voluntarily enabling 2fa', function () {
    $user = pengurus();
    $this->actingAs($user, 'web')->get('/admin/settings/security/2fa/enable');

    $response = $this->post('/admin/settings/security/2fa/enable', ['code' => '000000']);

    $response->assertSessionHasErrors('code');
    expect($user->fresh()->hasEnabledTwoFactor())->toBeFalse();
});

it('disables 2fa with the correct current password', function () {
    $user = pengurus();
    enableTwoFactorFor($user);

    $response = $this->actingAs($user, 'web')
        ->post('/admin/settings/security/2fa/disable', ['current_password' => 'password123']);

    $response->assertRedirect(route('admin.settings.security.index'));
    expect($user->fresh()->hasEnabledTwoFactor())->toBeFalse();
});

it('refuses to disable 2fa with the wrong password', function () {
    $user = pengurus();
    enableTwoFactorFor($user);

    $response = $this->actingAs($user, 'web')
        ->post('/admin/settings/security/2fa/disable', ['current_password' => 'wrong-password']);

    $response->assertSessionHasErrors('current_password');
    expect($user->fresh()->hasEnabledTwoFactor())->toBeTrue();
});

it('routes a superadmin back through mandatory setup after disabling 2fa', function () {
    $user = User::factory()->create(['role' => UserRole::SUPERADMIN, 'password' => bcrypt('password123')]);
    enableTwoFactorFor($user);

    $this->actingAs($user, 'web')
        ->post('/admin/settings/security/2fa/disable', ['current_password' => 'password123']);

    $this->post('/admin/logout');

    $response = $this->post('/admin/login', ['email' => $user->email, 'password' => 'password123']);

    $response->assertRedirect(route('admin.2fa.setup'));
});
