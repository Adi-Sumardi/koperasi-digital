<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_kyc', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('member_id')->unique()->constrained('members')->restrictOnDelete();
            $table->string('ktp_photo_path');
            $table->string('selfie_ktp_path');
            $table->string('status', 32)->default('pending');
            $table->string('rejection_reason')->nullable();
            $table->foreignUuid('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('verified_at')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_kyc');
    }
};
