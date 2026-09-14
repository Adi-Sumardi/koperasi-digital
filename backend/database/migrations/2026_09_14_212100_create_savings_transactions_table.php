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
        Schema::create('savings_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('savings_account_id')->constrained('savings_accounts')->restrictOnDelete();
            $table->string('reference_no', 64)->unique();
            $table->string('type', 32);
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->text('notes')->nullable();
            $table->timestampTz('created_at')->useCurrent();

            $table->index(['savings_account_id', 'created_at']);
        });

        DB::statement('ALTER TABLE savings_transactions ADD CONSTRAINT chk_transaction_amount_gt_zero CHECK (amount > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('savings_transactions');
    }
};
