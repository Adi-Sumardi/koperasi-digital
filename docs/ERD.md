# Diagram Relasi Entitas & Skema Basis Data (ERD.md)
## Sistem Koperasi Digital (*KopKar Digital*)

* **Database Engine**: PostgreSQL 16+
* **Presisi Moneter**: `DECIMAL(15, 2)` (Zero Float)
* **Kunci Utama**: `UUID` (v7)

---

## 1. Diagram Relasi Entitas (*Entity Relationship Diagram*)

```mermaid
erDiagram
    USERS ||--o| MEMBERS : "has profile"
    USERS ||--o{ AUDIT_LOGS : "acts in"
    MEMBERS ||--o| MEMBER_KYC : "submits"
    MEMBERS ||--o{ SAVINGS_ACCOUNTS : "owns"
    MEMBERS ||--o{ LOAN_APPLICATIONS : "applies"
    MEMBERS ||--o{ LOANS : "has"
    MEMBERS ||--o{ ORDERS : "purchases"
    MEMBERS ||--o{ RAT_ATTENDANCES : "attends"
    MEMBERS ||--o{ RAT_VOTES : "casts vote"

    SAVINGS_ACCOUNTS ||--o{ SAVINGS_TRANSACTIONS : "records"
    
    LOAN_PRODUCTS ||--o{ LOAN_APPLICATIONS : "offered as"
    LOAN_APPLICATIONS ||--o| LOANS : "approved to"
    LOAN_APPLICATIONS ||--o{ LOAN_APPROVALS : "reviewed by"
    LOANS ||--o{ LOAN_INSTALLMENTS : "amortized into"

    CHART_OF_ACCOUNTS ||--o{ JOURNAL_LINES : "categorized by"
    JOURNAL_ENTRIES ||--|{ JOURNAL_LINES : "contains"
    
    PRODUCT_CATEGORIES ||--o{ PRODUCTS : "groups"
    PRODUCTS ||--o{ ORDER_ITEMS : "ordered in"
    ORDERS ||--|{ ORDER_ITEMS : "includes"
    ORDERS ||--o| PAYMENT_INVOICES : "billed via"

    PAYMENT_INVOICES ||--o| QRIS_TRANSACTIONS : "settled via"

    RAT_SESSIONS ||--o{ RAT_AGENDAS : "discusses"
    RAT_SESSIONS ||--o{ RAT_ATTENDANCES : "records"
    RAT_AGENDAS ||--o{ RAT_VOTES : "tallies"

    USERS {
        uuid id PK
        string email UK
        string phone_number UK
        string password_hash
        string transaction_pin_hash
        string role
        boolean is_active
        timestamptz created_at
    }

    MEMBERS {
        uuid id PK
        uuid user_id FK
        string member_number UK
        string full_name
        string nik UK
        string employee_nip UK
        string department
        string bank_name
        string bank_account_number
        decimal monthly_salary
        string status
        timestamptz joined_at
    }

    MEMBER_KYC {
        uuid id PK
        uuid member_id FK
        string ktp_photo_path
        string selfie_ktp_path
        string status
        string rejection_reason
        uuid verified_by FK
        timestamptz verified_at
    }

    SAVINGS_ACCOUNTS {
        uuid id PK
        uuid member_id FK
        string account_number UK
        string type
        decimal balance
        string status
        timestamptz created_at
    }

    SAVINGS_TRANSACTIONS {
        uuid id PK
        uuid savings_account_id FK
        string reference_no UK
        string type
        decimal amount
        decimal balance_after
        string notes
        timestamptz created_at
    }

    LOAN_PRODUCTS {
        uuid id PK
        string name
        string interest_type
        decimal annual_interest_rate
        integer min_tenor_months
        integer max_tenor_months
        decimal max_ceiling_amount
        boolean is_active
    }

    LOANS {
        uuid id PK
        uuid member_id FK
        uuid loan_application_id FK
        string loan_number UK
        decimal principal_amount
        decimal interest_rate
        integer tenor_months
        decimal monthly_installment
        decimal outstanding_balance
        string status
        timestamptz disbursed_at
    }

    LOAN_INSTALLMENTS {
        uuid id PK
        uuid loan_id FK
        integer installment_number
        date due_date
        decimal principal_portion
        decimal interest_portion
        decimal total_installment
        decimal paid_amount
        string status
        timestamptz paid_at
    }

    CHART_OF_ACCOUNTS {
        uuid id PK
        string code UK
        string name
        string account_type
        string normal_balance
        boolean is_active
    }

    JOURNAL_ENTRIES {
        uuid id PK
        string entry_number UK
        date entry_date
        string reference_type
        uuid reference_id
        string description
        boolean is_posted
        timestamptz created_at
    }

    JOURNAL_LINES {
        uuid id PK
        uuid journal_entry_id FK
        uuid account_id FK
        decimal debit
        decimal credit
        string notes
    }

    RAT_SESSIONS {
        uuid id PK
        integer fiscal_year
        string title
        timestamptz start_time
        timestamptz end_time
        string status
        string lpj_document_path
    }

    RAT_AGENDAS {
        uuid id PK
        uuid rat_session_id FK
        string title
        text description
        integer order_index
        string status
    }

    RAT_VOTES {
        uuid id PK
        uuid rat_agenda_id FK
        uuid member_id FK
        string vote_choice
        string signature_hash
        timestamptz voted_at
    }

    QRIS_TRANSACTIONS {
        uuid id PK
        string transaction_reference UK
        string merchant_name
        string nmid
        decimal amount
        decimal fee_amount
        string status
        timestamptz settled_at
    }
```

---

## 2. Kamus Data & Spesifikasi Kolom (*Data Dictionary*)

### 2.1. Tabel `users` & `members`
Tabel utama autentikasi dan profil keanggotaan koperasi karyawan:

```sql
CREATE TABLE users (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    email VARCHAR(255) UNIQUE NOT NULL,
    phone_number VARCHAR(32) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    transaction_pin_hash VARCHAR(255),
    role VARCHAR(32) NOT NULL DEFAULT 'member', -- member, treasurer, chairman, auditor, superadmin
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE members (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    member_number VARCHAR(32) UNIQUE NOT NULL, -- KOP-2026-00001
    full_name VARCHAR(255) NOT NULL,
    nik VARCHAR(255) UNIQUE NOT NULL, -- Enkripsi PII AES-256
    employee_nip VARCHAR(64) UNIQUE NOT NULL,
    department VARCHAR(128) NOT NULL,
    bank_name VARCHAR(64) NOT NULL,
    bank_account_number VARCHAR(255) NOT NULL, -- Enkripsi PII
    monthly_salary DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    status VARCHAR(32) NOT NULL DEFAULT 'pending', -- pending, active, resigned, suspended
    joined_at TIMESTAMPTZ,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);
```

---

### 2.2. Tabel `savings_accounts` & `savings_transactions`
Mengelola rekening simpanan anggota (Pokok, Wajib, Sukarela):

```sql
CREATE TABLE savings_accounts (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    member_id UUID NOT NULL REFERENCES members(id) ON DELETE RESTRICT,
    account_number VARCHAR(32) UNIQUE NOT NULL,
    type VARCHAR(32) NOT NULL, -- pokok, wajib, sukarela
    balance DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    status VARCHAR(32) NOT NULL DEFAULT 'active',
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    CONSTRAINT chk_savings_balance_positive CHECK (balance >= 0)
);

CREATE TABLE savings_transactions (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    savings_account_id UUID NOT NULL REFERENCES savings_accounts(id) ON DELETE RESTRICT,
    reference_no VARCHAR(64) UNIQUE NOT NULL,
    type VARCHAR(32) NOT NULL, -- deposit, withdrawal, interest, transfer
    amount DECIMAL(15, 2) NOT NULL,
    balance_after DECIMAL(15, 2) NOT NULL,
    notes TEXT,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    CONSTRAINT chk_transaction_amount_gt_zero CHECK (amount > 0)
);
```

---

### 2.3. Tabel `loans` & `loan_installments`
Mengelola pinjaman anggota dan jadwal angsuran amortisasi:

```sql
CREATE TABLE loans (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    member_id UUID NOT NULL REFERENCES members(id) ON DELETE RESTRICT,
    loan_number VARCHAR(32) UNIQUE NOT NULL,
    principal_amount DECIMAL(15, 2) NOT NULL,
    interest_rate DECIMAL(6, 4) NOT NULL, -- misal: 0.0080 (0.8% per bulan)
    tenor_months INTEGER NOT NULL,
    monthly_installment DECIMAL(15, 2) NOT NULL,
    outstanding_balance DECIMAL(15, 2) NOT NULL,
    status VARCHAR(32) NOT NULL DEFAULT 'active', -- active, paid_off, defaulted
    disbursed_at TIMESTAMPTZ,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    CONSTRAINT chk_principal_positive CHECK (principal_amount > 0),
    CONSTRAINT chk_outstanding_non_negative CHECK (outstanding_balance >= 0)
);

CREATE TABLE loan_installments (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    loan_id UUID NOT NULL REFERENCES loans(id) ON DELETE RESTRICT,
    installment_number INTEGER NOT NULL,
    due_date DATE NOT NULL,
    principal_portion DECIMAL(15, 2) NOT NULL,
    interest_portion DECIMAL(15, 2) NOT NULL,
    total_installment DECIMAL(15, 2) NOT NULL,
    paid_amount DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    status VARCHAR(32) NOT NULL DEFAULT 'unpaid', -- unpaid, partial, paid, overdue
    paid_at TIMESTAMPTZ,
    CONSTRAINT uq_loan_installment UNIQUE (loan_id, installment_number)
);
```

---

### 2.4. Tabel Akuntansi Berpasangan (`chart_of_accounts`, `journal_entries`, `journal_lines`)
Menjamin kepatuhan standar pembukuan SAK EP dan auditabilitas finansial:

```sql
CREATE TABLE chart_of_accounts (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    code VARCHAR(32) UNIQUE NOT NULL, -- 1-1000 Kas, 1-1100 Bank, 1-1200 Piutang Pinjaman, dll.
    name VARCHAR(128) NOT NULL,
    account_type VARCHAR(32) NOT NULL, -- asset, liability, equity, revenue, expense
    normal_balance VARCHAR(16) NOT NULL, -- debit, credit
    is_active BOOLEAN NOT NULL DEFAULT TRUE
);

CREATE TABLE journal_entries (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    entry_number VARCHAR(64) UNIQUE NOT NULL, -- JRN-20260914-0001
    entry_date DATE NOT NULL,
    reference_type VARCHAR(64), -- savings_transaction, loan, kopmart_order
    reference_id UUID,
    description TEXT NOT NULL,
    is_posted BOOLEAN NOT NULL DEFAULT TRUE,
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

### 2.5. Tabel Tata Kelola RAT (`rat_sessions`, `rat_agendas`, `rat_votes`)
Menjamin pemungutan suara sah berlandaskan asas *"One Member, One Vote"*:

```sql
CREATE TABLE rat_sessions (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    fiscal_year INTEGER NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    start_time TIMESTAMPTZ NOT NULL,
    end_time TIMESTAMPTZ NOT NULL,
    status VARCHAR(32) NOT NULL DEFAULT 'draft', -- draft, active, closed
    lpj_document_path VARCHAR(255)
);

CREATE TABLE rat_agendas (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    rat_session_id UUID NOT NULL REFERENCES rat_sessions(id) ON DELETE RESTRICT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    order_index INTEGER NOT NULL DEFAULT 1,
    status VARCHAR(32) NOT NULL DEFAULT 'open'
);

CREATE TABLE rat_votes (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    rat_agenda_id UUID NOT NULL REFERENCES rat_agendas(id) ON DELETE RESTRICT,
    member_id UUID NOT NULL REFERENCES members(id) ON DELETE RESTRICT,
    vote_choice VARCHAR(16) NOT NULL, -- agree, disagree, abstain
    signature_hash VARCHAR(255) NOT NULL, -- Hash pengaman integritas suara
    voted_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    CONSTRAINT uq_member_agenda_vote UNIQUE (rat_agenda_id, member_id)
);
```

---

## 3. Aturan Keseimbangan Debit-Kredit (*Double-Entry Balancing Trigger*)

Fungsi database trigger untuk memastikan sebuah `journal_entry` tidak dapat diposting jika total Debit $\neq$ total Kredit:

```sql
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
```
