<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('logs in with valid credentials and issues a token', function () {
    User::factory()->create([
        'email' => 'budi@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'budi@example.com',
        'password' => 'password123',
        'device_name' => 'pixel-8',
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data' => ['token', 'user']]);
});

it('rejects login with an invalid password', function () {
    User::factory()->create([
        'email' => 'budi@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'budi@example.com',
        'password' => 'wrong-password',
        'device_name' => 'pixel-8',
    ]);

    $response->assertUnauthorized();
});

it('rejects login for an inactive user', function () {
    User::factory()->create([
        'email' => 'budi@example.com',
        'password' => Hash::make('password123'),
        'is_active' => false,
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'budi@example.com',
        'password' => 'password123',
        'device_name' => 'pixel-8',
    ]);

    $response->assertForbidden();
});
