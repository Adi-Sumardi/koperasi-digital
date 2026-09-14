# Arsitektur Sistem & Desain Teknis (arsitektur.md)
## Sistem Koperasi Digital (*KopKar Digital*)

* **Arsitektur**: Headless Decoupled Micro-Modular
* **Backend**: Laravel 13 (REST API)
* **Frontend**: Flutter (Mobile Client Android & iOS)
* **Database**: PostgreSQL 16+ (ACID Single Source of Truth)

---

## 1. Diagram Arsitektur Tingkat Tinggi (*C4 Container Diagram*)

```mermaid
graph TB
    subgraph Klien Berdasarkan Peran
        A1[Anggota Koperasi<br/>Aplikasi Mobile Flutter<br/>Android & iOS]
        A2[Super Admin & Pengurus<br/>Portal Web Backoffice<br/>Browser Desktop / Laptop]
    end

    subgraph Infrastruktur Keamanan & Jaringan
        NGINX[Nginx Reverse Proxy<br/>TLS 1.3 / Rate Limiter / WAF]
    end

    subgraph Layanan Inti Laravel 13
        WEB[Laravel 13 Web Application<br/>Blade Admin Backoffice & Session Auth]
        API[Laravel 13 REST API Core<br/>Stateless Sanctum Token & Idempotency]
        QUEUE[Laravel Horizon Workers<br/>Event & Ledger Dispatcher]
    end

    subgraph Lapisan Data & Penyimpanan
        DB[(PostgreSQL 16+<br/>ACID Financial Ledger)]
        CACHE[(Redis 7+<br/>Cache, Queues & Locks)]
        S3[(MinIO / S3<br/>Private KYC Encrypted Storage)]
    end

    subgraph Integrasi Eksternal
        PGW[Payment Gateway<br/>Virtual Account & QRIS]
        WAG[WhatsApp & Push Notification<br/>FCM & Fonnte Gateway]
    end

    A1 -->|HTTPS / JSON / Token Sanctum| NGINX
    A2 -->|HTTPS / Session Cookies / CSRF| NGINX
    NGINX -->|Akses /admin| WEB
    NGINX -->|Akses /api/v1| API
    WEB --> DB
    WEB --> CACHE
    API --> DB
    API --> CACHE
    API --> S3
    API --> QUEUE
    QUEUE --> DB
    API --> PGW
```

---

## 2. Pola Arsitektur Perangkat Lunak (*Clean Architecture*)

### 2.1. Lapisan Backend (Laravel 13)
Backend menerapkan pemisahan tanggung jawab berbasis *Domain-Driven Clean Architecture*:

```
backend/
├── app/
│   ├── Actions/               # Application Use Cases (Single Action Classes)
│   │   ├── Loans/             # ApplyLoanAction, ApproveLoanAction, DisburseLoanAction
│   │   ├── Savings/           # DepositSavingsAction, WithdrawSavingsAction
│   │   └── Accounting/        # PostJournalEntryAction
│   ├── Enums/                 # Backed Enums (LoanStatus, SavingsType, TransactionType)
│   ├── Events/                # Domain Events (SavingsDeposited, LoanApproved)
│   ├── Exceptions/            # Custom Domain Exceptions (InsufficientBalanceException)
│   ├── Http/
│   │   ├── Controllers/Api/V1/# Thin Invokable Controllers
│   │   ├── Requests/          # Form Requests dengan validasi ketat
│   │   └── Resources/         # JSON API Resources Transformer
│   ├── Listeners/             # Async Event Listeners (Buku besar, Notifikasi)
│   ├── Models/                # Eloquent Models dengan UUID & Type Casting
│   ├── Repositories/          # Abstraksi kueri database
│   └── Services/              # Layanan domain murni (Kalkulasi bunga, SHU)
```

### 2.2. Lapisan Mobile (Flutter)
Aplikasi mobile menerapkan *Feature-First Clean Architecture*:

```
mobile/
├── lib/
│   ├── core/                  # Network (Dio), storage, errors, formatters
│   ├── features/
│   │   ├── home/              # Beranda Anggota
│   │   ├── savings/           # Simpanan Pokok, Wajib, Sukarela
│   │   ├── loans/             # Pinjaman & Kalkulator
│   │   ├── kopmart/           # Belanja Karyawan
│   │   ├── qris/              # Scanner & Pembayaran QRIS
│   │   └── rat/               # RAT Digital & E-Voting
│   └── shared/                # Reusable widgets, themes, design tokens
```

---

## 3. Siklus Transaksi Finansial & Mesin Idempotensi

Setiap transaksi penambahan atau pemotongan dana anggota wajib melalui pipa pengamanan berikut:

```mermaid
sequenceDiagram
    autonumber
    actor User as Anggota (Flutter)
    participant Dio as Dio Interceptor
    participant Ctrl as Laravel Controller
    participant Lock as Redis Distributed Lock
    participant Action as DepositSavingsAction
    participant DB as PostgreSQL (ACID)
    participant Queue as Horizon Queue

    User->>Dio: Klik "Konfirmasi Setoran"
    Dio->>Dio: Generate X-Idempotency-Key (UUID v4)
    Dio->>Ctrl: POST /api/v1/savings/deposit
    Ctrl->>Lock: Periksa & Kunci Idempotency Key
    alt Key Duplikat / Sedang Diproses
        Lock-->>Ctrl: Key Terkunci
        Ctrl-->>User: 409 Conflict / Return Cached Response
    else Key Baru & Sah
        Lock->>Action: Eksekusi Logika Bisnis
        Action->>DB: BEGIN TRANSACTION
        Action->>DB: SELECT ... FOR UPDATE (Kunci Baris Rekening)
        Action->>DB: UPDATE saldo rekening anggota
        Action->>DB: INSERT into transactions
        Action->>DB: COMMIT TRANSACTION
        Action->>Queue: Dispatch SavingsDepositedEvent
        Action-->>Ctrl: Sukses
        Ctrl-->>User: 200 OK (Saldo Baru)
        Queue->>DB: Worker Posting Debit Kas & Kredit Liabilitas Simpanan
    end
```

---

## 4. Mesin Status Pinjaman (*Loan Lifecycle State Machine*)

```mermaid
stateDiagram-v2
    [*] --> DRAFT : Anggota Mengisi Form
    DRAFT --> PENDING_REVIEW : Ajukan Pinjaman (Submit)
    
    PENDING_REVIEW --> IN_COMMITTEE : Plafon > Rp 5.000.000
    PENDING_REVIEW --> APPROVED : Disetujui Analis/Bendahara
    PENDING_REVIEW --> REJECTED : Ditolak (Tidak Layak)
    
    IN_COMMITTEE --> APPROVED : Disetujui Komite & Ketua
    IN_COMMITTEE --> REJECTED : Ditolak Komite

    APPROVED --> DISBURSED : Dana Dicairkan ke Saldo Sukarela
    
    DISBURSED --> ACTIVE : Jadwal Angsuran Dimulai
    ACTIVE --> ACTIVE : Bayar Angsuran Bulanan
    ACTIVE --> PAID_OFF : Seluruh Pokok & Bunga Lunas
    ACTIVE --> DEFAULTED : Menunggak > 90 Hari (NPL)
    
    DEFAULTED --> ACTIVE : Restrukturisasi / Pelunasan
    PAID_OFF --> [*]
    REJECTED --> [*]
```

---

## 5. Cetak Biru Struktur Direktori Repositori (*Monorepo Blueprint*)

```
koperasi-digital/
├── .agents/                   # Aturan & Customizations Antigravity IDE
│   └── rules/                 # 17 Berkas Aturan Teknis
├── backend/                   # Backend Laravel 13 (Web Superadmin & API Mobile)
│   ├── app/
│   │   ├── Http/Controllers/Web/Admin/ # Controllers Web Backoffice Superadmin
│   │   └── Http/Controllers/Api/V1/    # Controllers REST API Mobile Anggota
│   ├── config/
│   ├── database/migrations/   # Migrasi Skema PostgreSQL
│   ├── resources/views/admin/ # Blade View Backoffice Superadmin
│   ├── routes/web.php         # Rute Portal Web Superadmin (/admin)
│   ├── routes/api.php         # Rute REST API Mobile Flutter (/api/v1)
│   └── tests/                 # Pest PHP Feature & Unit Tests
├── mobile/                    # Kode Sumber Aplikasi Flutter (Android/iOS)
│   ├── lib/
│   ├── assets/                # Font Plus Jakarta Sans, Inter, Icon SVG
│   └── test/                  # Widget & Unit Tests
├── design/                    # Aset Desain UI/UX Mobile Flutter
│   ├── beranda_anggota/       # HTML Mockup & Screenshot Beranda
│   ├── manajemen_simpanan/    # Layar Manajemen Simpanan
│   ├── layanan_pinjaman/      # Layar Pinjaman & Kalkulator
│   ├── kopmart_belanja_karyawan/
│   ├── bayar_via_qris/
│   ├── konfirmasi_bayar_qris/
│   ├── bukti_pembayaran_qris/
│   ├── riwayat_transaksi_mutasi/
│   └── kopkar_digital/        # Token Desain & Styling System
├── docker/                    # Konfigurasi Nginx & Dockerfile
├── rules/                     # Salinan 17 Berkas Aturan Resmi
├── PRD.md                     # Product Requirements Document
├── BRD.md                     # Business Requirements Document
├── ERD.md                     # Entity Relationship Diagram
├── techstack.md               # Spesifikasi Tumpukan Teknologi
├── arsitektur.md              # Arsitektur Sistem & Alur
├── AGENTS.md                  # Master Panduan AI Pair-Programmer
└── docker-compose.yml         # Orkestrasi Layanan Lokal
```
