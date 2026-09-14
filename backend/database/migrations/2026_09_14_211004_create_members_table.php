<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->unique()->constrained('users')->restrictOnDelete();

            // Diterbitkan saat keanggotaan diaktifkan (AKTIF), bukan saat registrasi.
            $table->string('member_number', 32)->nullable()->unique();

            $table->string('full_name');
            $table->text('nik');
            $table->char('nik_hash', 64)->unique();
            $table->string('employee_nip', 64)->unique();
            $table->string('department', 128);
            $table->string('bank_name', 64);
            $table->text('bank_account_number');
            $table->decimal('monthly_salary', 15, 2)->default(0);
            $table->string('status', 32)->default('pending');
            $table->timestampTz('joined_at')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
