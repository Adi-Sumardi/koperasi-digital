<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_approvals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('loan_application_id')->constrained('loan_applications')->restrictOnDelete();
            $table->foreignUuid('approver_id')->constrained('users')->restrictOnDelete();
            $table->string('decision', 16);
            $table->string('notes', 255)->nullable();
            $table->timestampTz('decided_at')->useCurrent();

            // Satu approver hanya boleh memutuskan sekali per pengajuan (rules/models.md §6 semangat integritas alur berjenjang).
            $table->unique(['loan_application_id', 'approver_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_approvals');
    }
};
