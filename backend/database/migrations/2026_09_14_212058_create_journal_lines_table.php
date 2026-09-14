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
        Schema::create('journal_lines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('journal_entry_id')->constrained('journal_entries')->restrictOnDelete();
            $table->foreignUuid('account_id')->constrained('chart_of_accounts')->restrictOnDelete();
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->string('notes', 255)->nullable();
        });

        DB::statement('ALTER TABLE journal_lines ADD CONSTRAINT chk_debit_credit_positive CHECK (debit >= 0 AND credit >= 0)');
        DB::statement('ALTER TABLE journal_lines ADD CONSTRAINT chk_debit_xor_credit CHECK ((debit > 0 AND credit = 0) OR (credit > 0 AND debit = 0))');

        // Setiap journal_entry hanya boleh ter-commit jika total debit = total kredit.
        // DEFERRABLE INITIALLY DEFERRED: validasi berjalan di akhir transaksi (COMMIT),
        // bukan per baris — karena kedua sisi (debit & kredit) disisipkan dalam INSERT terpisah.
        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION verify_journal_entry_balance()
            RETURNS TRIGGER AS $$
            DECLARE
                v_total_debit DECIMAL(15, 2);
                v_total_credit DECIMAL(15, 2);
            BEGIN
                SELECT COALESCE(SUM(debit), 0), COALESCE(SUM(credit), 0)
                INTO v_total_debit, v_total_credit
                FROM journal_lines
                WHERE journal_entry_id = NEW.journal_entry_id;

                IF v_total_debit <> v_total_credit THEN
                    RAISE EXCEPTION 'Ketidakseimbangan Jurnal: Total Debit (%) tidak sama dengan Total Kredit (%)',
                        v_total_debit, v_total_credit;
                END IF;

                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            CREATE CONSTRAINT TRIGGER trg_verify_journal_entry_balance
            AFTER INSERT ON journal_lines
            DEFERRABLE INITIALLY DEFERRED
            FOR EACH ROW
            EXECUTE FUNCTION verify_journal_entry_balance();
        SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_verify_journal_entry_balance ON journal_lines');
        DB::unprepared('DROP FUNCTION IF EXISTS verify_journal_entry_balance');

        Schema::dropIfExists('journal_lines');
    }
};
