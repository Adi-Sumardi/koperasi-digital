<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;

it('redirects unauthenticated visitors to the login page', function () {
    $this->get('/admin')->assertRedirect(route('admin.login'));
});

it('logs a pengurus in and redirects to the dashboard', function () {
    $treasurer = User::factory()->create(['role' => UserRole::TREASURER, 'password' => bcrypt('password123')]);

    $response = $this->post('/admin/login', [
        'email' => $treasurer->email,
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('admin.dashboard'));
    $this->assertAuthenticatedAs($treasurer, 'web');
});

it('rejects a member account even with correct credentials', function () {
    $member = memberWithSavingsAccounts();
    $member->user->update(['password' => bcrypt('password123')]);

    $response = $this->post('/admin/login', [
        'email' => $member->user->email,
        'password' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest('web');
});

it('rejects an inactive pengurus account', function () {
    $treasurer = User::factory()->create([
        'role' => UserRole::TREASURER,
        'password' => bcrypt('password123'),
        'is_active' => false,
    ]);

    $response = $this->post('/admin/login', [
        'email' => $treasurer->email,
        'password' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest('web');
});

it('logs the user out', function () {
    $treasurer = adminUser();

    $this->actingAs($treasurer, 'web')
        ->post('/admin/logout')
        ->assertRedirect(route('admin.login'));

    $this->assertGuest('web');
});

it('forbids a member role from accessing the admin dashboard', function () {
    $member = memberWithSavingsAccounts();

    $this->actingAs($member->user, 'web')
        ->get('/admin')
        ->assertForbidden();
});
