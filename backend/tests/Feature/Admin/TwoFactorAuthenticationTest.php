<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;
use App\Services\Security\TwoFactorAuthenticationService;
use PragmaRX\Google2FA\Google2FA;

function superadmin(): User
{
    return User::factory()->create(['role' => UserRole::SUPERADMIN, 'password' => bcrypt('password123')]);
}

it('sends a superadmin without 2fa configured to the setup page after correct password', function () {
    $user = superadmin();

    $response = $this->post('/admin/login', ['email' => $user->email, 'password' => 'password123']);

    $response->assertRedirect(route('admin.2fa.setup'));
    $this->assertGuest('web');
});

it('completes 2fa setup with a valid code and logs the superadmin in', function () {
    $user = superadmin();

    $this->post('/admin/login', ['email' => $user->email, 'password' => 'password123']);

    $setupPage = $this->get('/admin/2fa/setup');
    $setupPage->assertOk();

    $secret = session('admin_2fa_setup_secret');
    $code = app(Google2FA::class)->getCurrentOtp($secret);

    $response = $this->post('/admin/2fa/setup', ['code' => $code]);

    $response->assertOk()->assertSee('Kode Pemulihan', false);
    $this->assertAuthenticatedAs($user, 'web');

    expect($user->fresh()->hasEnabledTwoFactor())->toBeTrue();
});

it('rejects an invalid code during 2fa setup and does not log in', function () {
    $user = superadmin();
    $this->post('/admin/login', ['email' => $user->email, 'password' => 'password123']);
    $this->get('/admin/2fa/setup');

    $response = $this->post('/admin/2fa/setup', ['code' => '000000']);

    $response->assertSessionHasErrors('code');
    $this->assertGuest('web');
    expect($user->fresh()->hasEnabledTwoFactor())->toBeFalse();
});

it('sends a superadmin with 2fa already confirmed to the challenge page', function () {
    $user = superadmin();
    enableTwoFactorFor($user);

    $response = $this->post('/admin/login', ['email' => $user->email, 'password' => 'password123']);

    $response->assertRedirect(route('admin.2fa.challenge'));
    $this->assertGuest('web');
});

it('logs a superadmin in after a correct totp code at the challenge', function () {
    $user = superadmin();
    $secret = enableTwoFactorFor($user);

    $this->post('/admin/login', ['email' => $user->email, 'password' => 'password123']);

    $code = app(Google2FA::class)->getCurrentOtp($secret);
    $response = $this->post('/admin/2fa/challenge', ['code' => $code]);

    $response->assertRedirect(route('admin.dashboard'));
    $this->assertAuthenticatedAs($user, 'web');
});

it('rejects an incorrect code at the 2fa challenge', function () {
    $user = superadmin();
    enableTwoFactorFor($user);

    $this->post('/admin/login', ['email' => $user->email, 'password' => 'password123']);

    $response = $this->post('/admin/2fa/challenge', ['code' => '000000']);

    $response->assertSessionHasErrors('code');
    $this->assertGuest('web');
});

it('allows a one-time recovery code to be used exactly once', function () {
    $user = superadmin();
    $secret = enableTwoFactorFor($user);
    $totp = app(TwoFactorAuthenticationService::class);
    $plainCodes = $totp->generateRecoveryCodes(1);
    $user->forceFill(['two_factor_recovery_codes' => $totp->hashRecoveryCodes($plainCodes)])->save();

    $this->post('/admin/login', ['email' => $user->email, 'password' => 'password123']);
    $this->post('/admin/2fa/challenge', ['code' => $plainCodes[0]])
        ->assertRedirect(route('admin.dashboard'));
    $this->assertAuthenticatedAs($user, 'web');

    $this->post('/admin/logout');

    // Kode pemulihan yang sama tidak boleh bisa dipakai kedua kalinya.
    $this->post('/admin/login', ['email' => $user->email, 'password' => 'password123']);
    $reuse = $this->post('/admin/2fa/challenge', ['code' => $plainCodes[0]]);

    $reuse->assertSessionHasErrors('code');
    $this->assertGuest('web');
});

it('does not require 2fa for a non-superadmin pengurus', function () {
    $treasurer = User::factory()->create(['role' => UserRole::TREASURER, 'password' => bcrypt('password123')]);

    $response = $this->post('/admin/login', ['email' => $treasurer->email, 'password' => 'password123']);

    $response->assertRedirect(route('admin.dashboard'));
    $this->assertAuthenticatedAs($treasurer, 'web');
});
