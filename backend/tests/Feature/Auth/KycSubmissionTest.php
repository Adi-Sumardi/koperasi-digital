<?php

declare(strict_types=1);

use App\Models\Member;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('rejects unauthenticated kyc submission', function () {
    $response = $this->postJson('/api/v1/auth/kyc/submit', []);

    $response->assertUnauthorized();
});

it('uploads ktp and selfie photos to the private s3 disk', function () {
    Storage::fake('s3');

    $member = Member::factory()->for(User::factory())->create();

    $response = $this->actingAs($member->user, 'sanctum')->postJson('/api/v1/auth/kyc/submit', [
        'ktp_photo' => UploadedFile::fake()->image('ktp.jpg'),
        'selfie_ktp' => UploadedFile::fake()->image('selfie.jpg'),
    ]);

    $response->assertOk()->assertJsonPath('data.status', 'pending');

    Storage::disk('s3')->assertExists("kyc/{$member->id}/ktp.jpg");
    Storage::disk('s3')->assertExists("kyc/{$member->id}/selfie.jpg");

    expect($member->kyc()->first())->not->toBeNull();
});

it('rejects non-image files for kyc uploads', function () {
    Storage::fake('s3');

    $member = Member::factory()->for(User::factory())->create();

    $response = $this->actingAs($member->user, 'sanctum')->postJson('/api/v1/auth/kyc/submit', [
        'ktp_photo' => UploadedFile::fake()->create('ktp.pdf', 100),
        'selfie_ktp' => UploadedFile::fake()->image('selfie.jpg'),
    ]);

    $response->assertUnprocessable()->assertJsonValidationErrors('ktp_photo');
});
