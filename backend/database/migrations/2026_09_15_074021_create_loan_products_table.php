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
        Schema::create('loan_products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 128);
            $table->string('interest_type', 16);
            // Suku bunga TAHUNAN (rules/database.md §1: DECIMAL(6,4)); dikonversi ke
            // rate bulanan (÷12) saat kalkulasi (BRD.md §5.3.2 memakai rate bulanan).
            $table->decimal('annual_interest_rate', 6, 4);
            $table->unsignedInteger('min_tenor_months');
            $table->unsignedInteger('max_tenor_months');
            $table->decimal('max_ceiling_amount', 15, 2);
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
        });

        DB::statement('ALTER TABLE loan_products ADD CONSTRAINT chk_loan_tenor_positive CHECK (min_tenor_months > 0 AND max_tenor_months >= min_tenor_months)');
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_products');
    }
};
