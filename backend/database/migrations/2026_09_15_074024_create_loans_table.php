<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('member_id')->constrained('members')->restrictOnDelete();
            $table->foreignUuid('loan_application_id')->unique()->constrained('loan_applications')->restrictOnDelete();
            $table->string('loan_number', 32)->unique();
            $table->decimal('principal_amount', 15, 2);
            $table->decimal('interest_rate', 6, 4);
            $table->unsignedInteger('tenor_months');
            $table->decimal('monthly_installment', 15, 2);
            $table->decimal('outstanding_balance', 15, 2);
            $table->string('status', 32)->default('active');
            $table->timestampTz('disbursed_at')->nullable();
            $table->timestampsTz();

            $table->index(['member_id', 'status']);
        });

        DB::statement('ALTER TABLE loans ADD CONSTRAINT chk_principal_positive CHECK (principal_amount > 0)');
        DB::statement('ALTER TABLE loans ADD CONSTRAINT chk_outstanding_non_negative CHECK (outstanding_balance >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
