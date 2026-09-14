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
        Schema::create('savings_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('member_id')->constrained('members')->restrictOnDelete();
            $table->string('account_number', 32)->unique();
            $table->string('type', 32);
            $table->decimal('balance', 15, 2)->default(0);
            $table->string('status', 32)->default('active');
            $table->timestampsTz();

            $table->unique(['member_id', 'type']);
        });

        DB::statement('ALTER TABLE savings_accounts ADD CONSTRAINT chk_savings_balance_non_negative CHECK (balance >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('savings_accounts');
    }
};
