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
        Schema::create('loan_applications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('member_id')->constrained('members')->restrictOnDelete();
            $table->foreignUuid('loan_product_id')->constrained('loan_products')->restrictOnDelete();
            $table->string('application_number', 32)->unique();
            $table->decimal('amount', 15, 2);
            $table->unsignedInteger('tenor_months');
            $table->string('purpose', 500);
            $table->string('guarantee_type', 32)->nullable();
            $table->string('status', 32)->default('pending_review');
            $table->string('rejection_reason', 255)->nullable();
            $table->timestampTz('decided_at')->nullable();
            $table->timestampsTz();

            $table->index(['member_id', 'status']);
        });

        DB::statement('ALTER TABLE loan_applications ADD CONSTRAINT chk_loan_application_amount_positive CHECK (amount > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_applications');
    }
};
