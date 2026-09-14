# Aturan Pengembangan: Standar Database PostgreSQL

Dokumen ini merumuskan standar konfigurasi, perancangan skema, tipe data, indeks, dan penguncian transaksi (*concurrency control*) pada PostgreSQL 16+ untuk sistem Koperasi Digital.

---

## 1. Aturan Tipe Data Kolom (*Data Types*)

| Kebutuhan Data | Tipe Data PostgreSQL | Alasan & Batasan |
| :--- | :--- | :--- |
| **Nominal Uang (Saldo, Transaksi, Pinjaman)** | `DECIMAL(15, 2)` | Presisi eksak. Menampung hingga Rp 999 Triliun dengan 2 digit sen. **Dilarang memakai FLOAT/REAL**. |
| **Persentase Bunga & Pembagian SHU** | `DECIMAL(6, 4)` | Presisi hingga 4 desimal (contoh: `0.0080` untuk 0,80%). |
| **Kunci Utama (Primary Key)** | `UUID` (v7) | Keamanan dari enumerasi, ramah pengindeksan waktu. |
| **Waktu & Tanggal** | `TIMESTAMPTZ` | Timestamp dengan zona waktu untuk rekonsiliasi lintas waktu. |
| **Metadata / Riwayat Payload / Snapshot** | `JSONB` | Mendukung kueri indeks GIN dan penyimpanan fleksibel. |
| **Status / Tipe Entitas** | `VARCHAR(32)` | Disandingkan dengan validasi PHP Backed Enum. |

---

## 2. Integritas Relasional & *Check Constraints*

Terapkan *Database-level Constraints* untuk mencegah anomali data di luar aplikasi:

```sql
-- 1. Mencegah Saldo Negatif pada Simpanan Sukarela (No Overdraft)
ALTER TABLE savings_accounts 
ADD CONSTRAINT chk_savings_balance_non_negative 
CHECK (balance >= 0);

-- 2. Memastikan Nominal Transaksi Selalu Positif
ALTER TABLE transactions 
ADD CONSTRAINT chk_transaction_amount_positive 
CHECK (amount > 0);

-- 3. Memastikan Suku Bunga dan Tenor Masuk Akal
ALTER TABLE loan_products 
ADD CONSTRAINT chk_loan_tenor_positive 
CHECK (min_tenor_months > 0 AND max_tenor_months >= min_tenor_months);
```

---

## 3. Pencegahan *Race Condition* & Penanganan Konkurensi

Semua operasi yang mengubah saldo anggota (setoran, penarikan, transfer, pelunasan) **wajib** menggunakan *pessimistic locking* (`FOR UPDATE`) di dalam blok `DB::transaction`:

```php
use Illuminate\Support\Facades\DB;

DB::transaction(function () use ($memberId, $amount) {
    // Kunci baris rekening simpanan anggota untuk mencegah transaksi paralel
    $savingsAccount = SavingsAccount::where('member_id', $memberId)
        ->where('type', SavingsType::SUKARELA)
        ->lockForUpdate()
        ->firstOrFail();

    if ($savingsAccount->balance < $amount) {
        throw new InsufficientBalanceException('Saldo simpanan sukarela tidak mencukupi.');
    }

    // Kurangi saldo
    $savingsAccount->decrement('balance', $amount);

    // Catat mutasi jurnal dan transaksi
    // ...
});
```

---

## 4. Standar Pembukuan Berpasangan (*Double-Entry Constraint*)

Setiap entri jurnal akuntansi (`journal_entries`) harus memiliki rincian debit-kredit (`journal_lines`) di mana jumlah total debit harus tepat sama dengan total kredit.

```sql
-- Skema Tabel Jurnal Akuntansi
CREATE TABLE journal_entries (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    reference_no VARCHAR(64) UNIQUE NOT NULL,
    entry_date DATE NOT NULL,
    description TEXT NOT NULL,
    is_posted BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE journal_lines (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    journal_entry_id UUID NOT NULL REFERENCES journal_entries(id) ON DELETE RESTRICT,
    account_id UUID NOT NULL REFERENCES chart_of_accounts(id) ON DELETE RESTRICT,
    debit DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    credit DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    notes VARCHAR(255),
    CONSTRAINT chk_debit_credit_positive CHECK (debit >= 0 AND credit >= 0),
    CONSTRAINT chk_debit_xor_credit CHECK ((debit > 0 AND credit = 0) OR (credit > 0 AND debit = 0))
);
```

---

## 5. Strategi Pengindeksan (*Indexing Strategy*)

1. **Foreign Key Index**: Setiap kolom *foreign key* wajib memiliki indeks tersendiri untuk mencegah *table lock* saat operasi *delete/update* relasi induk.
2. **Composite Index untuk Filter Frekuensi Tinggi**:
   * `CREATE INDEX idx_loans_member_status ON loans (member_id, status);`
   * `CREATE INDEX idx_transactions_account_date ON transactions (account_id, created_at DESC);`
3. **Partial Index untuk Data Aktif / Antrian**:
   * `CREATE INDEX idx_pending_loans ON loan_applications (created_at) WHERE status = 'pending';`
