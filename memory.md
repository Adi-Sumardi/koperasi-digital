# Log Kemajuan Proyek (memory.md) - Koperasi Digital

Dokumen ini mencatat setiap langkah kemajuan (*progress*) pembangunan aplikasi Koperasi Digital untuk referensi dan pelacakan.

---

## 📅 14 September 2026

### 1. Perombakan Total Proyek (*Complete Overhaul*)
* **Pembersihan Kode Lama**:
  * Menghapus seluruh artefak warisan frontend React/Vite (`src/`, `node_modules/`, `dist/`, `package.json`, `package-lock.json`, `tsconfig*.json`, `vite.config.ts`, `index.html`, `ui-ux/`, dll.).
  * Menjaga integritas folder `design/` yang menampung 8 prototipe layar Flutter dan desain sistem `kopkar_digital`.
* **Penetapan Tumpukan Teknologi Baru**:
  * **Backend**: Laravel 13 (PHP 8.3/8.4, Laravel Sanctum, Laravel Horizon, Pest PHP).
  * **Frontend**: Flutter 3.x (Dart 3.x, Feature-First Clean Architecture, Riverpod/BLoC, Dio, GoRouter).
  * **Database**: PostgreSQL 16+ (ACID, strict double-entry ledger, UUID v7, Decimal(15,2), pessimistic row locking).
  * **Cache & Queues**: Redis 7+.
  * **Object Storage**: MinIO / S3 Private.
* **Penyusunan 17 Dokumen Aturan Rekayasa (*Rules*) di `rules/` & `.agents/rules/`**:
  1. `overview.md` — Filosofi, prinsip integritas finansial & kepatuhan UU No. 25/1992 & P2SK.
  2. `design.md` — Design tokens (warna Sapphire & Cobalt, font Plus Jakarta Sans & Inter, 8pt grid).
  3. `components.md` — Spesifikasi widget reusable Flutter (FinancialHeroCard, StatusBadge, RupiahTextField, BottomBar).
  4. `transalations.md` — Glosarium istilah baku perkoperasian Indonesia (Simpanan Pokok/Wajib/Sukarela, SHU, RAT, Kuorum).
  5. `formatting.md` — Standar pemformatan Rupiah (`Rp 1.500.000`), tanggal WIB, NIK, dan nomor referensi transaksi.
  6. `controllers.md` — Standar Thin Controllers Laravel 13, Invokable Actions, Form Request & API Resources.
  7. `models.md` — Standar Eloquent Model (UUID v7, PHP Enums, Type Casting, Immutability Guards).
  8. `database.md` — Standar PostgreSQL (Decimal 15,2, Check Constraints, Row Locking, Trigger Seimbang).
  9. `views.md` — Standar tampilan presentasi Flutter (4 UI states: Loading, Success, Empty, Error) & Backoffice web.
  10. `api.md` — Standar RESTful JSON API (Envelope response, endpoint map, versioning, header `X-Idempotency-Key`).
  11. `code-style.md` — Konvensi gaya kode: PSR-12, Laravel Pint, Effective Dart, Conventional Commits.
  12. `security.md` — Standar keamanan finansial: PIN 6-digit, enkripsi PII, presigned URL KYC, audit log mutlak.
  13. `testing.md` — Strategi pengujian: Pest PHP untuk kalkulasi bunga/SHU & Flutter widget/unit testing.
  14. `development-plan.md` — Roadmap sprint 1-6, strategi percabangan Git Flow, dan kriteria acceptance PR.
  15. `flutter-app.md` — Blueprint arsitektur mobile Flutter: Feature-First Clean Architecture, Riverpod, Dio, GoRouter.
  16. `dispatch-engine.md` — Engine transaksi event-driven, Laravel Horizon, dan posting buku besar asinkron.
  17. `depleyment.md` — Panduan penerapan: Docker Compose, Nginx, PostgreSQL, Redis, CI/CD, dan build mobile release.
* **Penyusunan 5 Dokumen Inti Proyek di Root**:
  * `PRD.md` — Product Requirements Document (fitur detail, user personas, alur pengguna, kriteria penerimaan).
  * `BRD.md` — Business Requirements Document (visi, regulasi, alur simpanan, pinjaman flat/sliding, rumus SHU, tata kelola RAT).
  * `ERD.md` — Entity Relationship Diagram (diagram Mermaid lengkap, skema relasional PostgreSQL, kamus data detail, konstrain double-entry).
  * `techstack.md` — Spesifikasi tumpukan teknologi terperinci.
  * `arsitektur.md` — Blueprint arsitektur sistem C4, siklus transaksi finansial idempotency, dan state machine pinjaman.
* **Penataan Folder UI/UX Desain & Aturan Kepatuhan 1:1**:
  * Membuat `design/README.md` sebagai katalog 8 layar desain yang ada dan panduan upload desain baru dari pengguna.
  * Menetapkan aturan mutlak: seluruh widget dan layar Flutter wajib menyesuaikan 1:1 dengan contoh visual (`screen.png`) dan prototipe kode (`code.html`) di folder `design/`.
* **Pemisahan Kanal Akses Berdasarkan Peran**:
  * Super Admin & Pengurus beroperasi khusus via portal **Web Laravel** (Desktop/Laptop).
  * Anggota Koperasi (Karyawan) beroperasi khusus via aplikasi **Mobile Flutter** (Android/iOS).
* **Master Guide Entrypoint**:
  * Membuat `AGENTS.md` di root workspace sebagai acuan utama bagi developer dan AI pair programmer.

### 2. Setup Infrastruktur & Dependensi Backend Laravel 13
* **Infrastruktur Lokal via Docker Compose** (`docker-compose.yml` di root):
  * PostgreSQL 16 (`kopkar_postgres`) di port host `5433` (port `5432` lokal sudah dipakai Postgres.app), database `koperasi_digital` + `koperasi_digital_test`.
  * MinIO (`kopkar_minio`) di port `9000`/`9001` dengan bucket `koperasi-digital` sudah dibuat.
  * Redis memakai instance Homebrew lokal yang sudah berjalan di port `6379` (container Redis dilepas dari compose untuk menghindari bentrok port).
* **Konfigurasi `.env` & `.env.example`**: `DB_CONNECTION=pgsql`, `CACHE_STORE`/`SESSION_DRIVER`/`QUEUE_CONNECTION=redis`, `FILESYSTEM_DISK=s3` mengarah ke MinIO, `REDIS_CLIENT=predis` (ekstensi `phpredis` belum tersedia di PHP lokal 8.5.3), timezone aplikasi diubah ke `Asia/Jakarta`.
* **Paket Composer Terpasang**: `laravel/sanctum`, `laravel/horizon`, `spatie/laravel-permission`, `ramsey/uuid`, `barryvdh/laravel-dompdf`, `maatwebsite/excel:^4.0`, `league/flysystem-aws-s3-v3`, `predis/predis`, `pestphp/pest:^4.0` + `pest-plugin-laravel:^4.0` (naik dari versi di `techstack.md` karena PHP 8.5 lokal tidak kompatibel dengan `maatwebsite/excel:^3.1`/`pestphp/pest:^3.0`; `techstack.md` & `docs/TechStack.md` sudah diperbarui dengan catatan ini).
* **Scaffolding Laravel**: `install:api` (routes/api.php + migrasi Sanctum), trait `HasApiTokens` & `HasRoles` ditambahkan ke `User`, Horizon & spatie/laravel-permission ter-publish dan migrasinya sudah dijalankan.
* **Pengujian**: Pest diinisialisasi (`vendor/bin/pest --init`), `RefreshDatabase` diaktifkan di `tests/Pest.php`, `phpunit.xml` diarahkan ke database Postgres nyata (`koperasi_digital_test`) alih-alih SQLite in-memory — supaya constraint & trigger khas PostgreSQL (lihat `rules/database.md`) benar-benar teruji.
* **Verifikasi End-to-End**: koneksi DB, Redis, Cache, dan Storage (S3/MinIO) sudah dites lewat `tinker` dan berhasil; Horizon berhasil start/terminate; `vendor/bin/pint` bersih; `php artisan test` lulus.
* **Belum Dikerjakan (lanjutan Sprint 1)**: migrasi tabel inti (`members`, `member_kyc`, dll. sesuai ERD), endpoint registrasi/KYC, dan inisialisasi repositori Git (belum ada `.git` sama sekali di root proyek).

### 3. Inisialisasi Git & Sprint 1: Fondasi Auth + KYC
* **Repositori Git**: `git init` di root, remote `origin` diarahkan ke `https://github.com/Adi-Sumardi/koperasi-digital.git`, commit awal (dokumen + setup backend) di-push ke branch `main`.
* **Migrasi Skema Inti** (mengikuti `ERD.md`, dengan `users.id`/`members.id`/`member_kyc.id` sebagai UUID v7 primary key — migrasi Sanctum & spatie/laravel-permission ikut disesuaikan ke `uuidMorphs`/`uuid('model_id')` supaya relasi morph tetap valid):
  * `users`: tambah `phone_number` (unique), `transaction_pin_hash`, `role`, `is_active`.
  * `members`: `member_number` (nullable — baru diterbitkan saat aktivasi, bukan saat registrasi, sesuai alur di `BRD.md` §5.1), `nik` (terenkripsi) + `nik_hash` (HMAC-SHA256 deterministik, unique) untuk deduplikasi NIK tanpa membongkar enkripsi, `bank_account_number` terenkripsi.
  * `member_kyc`: relasi 1:1 ke `members`, `status` (pending/approved/rejected), path foto KTP & selfie di disk privat `s3` (MinIO).
* **Model & Enum**: `App\Models\{Member,MemberKyc}` + `App\Enums\{UserRole,MemberStatus,KycStatus}`; `User` memakai `HasUuids`, `HasApiTokens`, `HasRoles`. `Member::nik()` adalah custom Attribute yang meng-enkripsi & mengisi `nik_hash` sekaligus (lihat catatan gotcha di bawah).
* **Endpoint API v1** (`routes/api.php`, namespace `App\Http\Controllers\Api\V1\Auth`, invokable controllers + Form Request + Action, respons via `App\Support\ApiResponse` sesuai amplop di `rules/api.md`):
  * `POST /api/v1/auth/register` — bikin `User`+`Member` (status `pending`), langsung terbitkan token Sanctum.
  * `POST /api/v1/auth/login`, `POST /api/v1/auth/logout` (auth), `GET /api/v1/auth/me` (auth).
  * `POST /api/v1/auth/kyc/submit` (auth) — upload `ktp_photo`+`selfie_ktp` (image, max 5MB) ke MinIO privat `kyc/{member_id}/...`, `updateOrCreate` record `MemberKyc`.
* **Gotcha teknis dicatat**: mutator custom Eloquent yang perlu mengisi kolom lain (mis. `nik_hash` di samping `nik`) **tidak boleh** menulis `$this->attributes[...]` sebagai side-effect di dalam closure `set` — `array_merge()` di `HasAttributes::setAttributeMarkedMutatedAttributeValue()` mengevaluasi `$this->attributes` (operand pertama) SEBELUM closure-nya jalan, jadi side-effect itu hilang tertimpa. Solusi benar: closure `set` mengembalikan array asosiatif `['nik' => ..., 'nik_hash' => ...]` (didukung native oleh `normalizeCastClassResponse`).
* **Testing**: `UserFactory` & `MemberFactory` baru, 10 test Pest baru di `tests/Feature/Auth/` (register/login/kyc) — total 12 test lulus. Seluruh alur diverifikasi manual end-to-end via `curl` (register → duplikat NIK ditolak → login → me → upload KYC ke MinIO → me menampilkan kyc → logout → token tercabut) sebelum ditulis sebagai test otomatis.
* **Belum dikerjakan**: endpoint approval/reject KYC oleh pengurus (belum ada di `rules/api.md`, kemungkinan bagian dari backoffice Web Admin yang belum dibangun), penerbitan `member_number` saat aktivasi (baru terjadi setelah Simpanan Pokok lunas — Sprint 2), dan seluruh sisi Flutter (Auth/Registrasi/KYC Pending) untuk Sprint 1.

### 4. Sprint 2: Manajemen Simpanan & Mesin Akuntansi Berpasangan
* **Arsitektur mengikuti `rules/dispatch-engine.md` persis**: fase sinkron (validasi + `lockForUpdate()` + update saldo + catat `SavingsTransaction` + dispatch event) di dalam `DB::transaction`, lalu fase asinkron via listener `ShouldQueue` di queue `financial-ledger` yang memposting jurnal berpasangan lewat `JournalPostingService`. `config/horizon.php` diperbarui agar supervisor default benar-benar mengerjakan queue `high-financial`, `financial-ledger`, `notifications`, `reports` (sebelumnya hanya `default` — kalau tidak diperbaiki, job akuntansi tidak akan pernah diproses Horizon di staging/produksi).
* **Migrasi baru**: `chart_of_accounts`, `journal_entries`, `journal_lines` (UUID v7, sesuai `ERD.md` §2.4), `savings_accounts`, `savings_transactions`. Semua CHECK constraint dipasang via `DB::statement('ALTER TABLE ... ADD CONSTRAINT ... CHECK (...)')` di migration karena Laravel 13 Blueprint **tidak punya** method `$table->check()` native (sempat dicoba dan error `BadMethodCallException`).
* **Trigger keseimbangan debit=kredit** (`verify_journal_entry_balance`, ERD.md §3) dibuat sebagai **`CREATE CONSTRAINT TRIGGER ... DEFERRABLE INITIALLY DEFERRED`**, bukan trigger biasa — karena dua baris jurnal (debit & kredit) disisipkan lewat dua `INSERT` terpisah dalam satu transaksi; trigger biasa (immediate) akan gagal di baris pertama sebelum baris kedua sempat masuk. Sudah diverifikasi lewat tinker: entry tak seimbang di-rollback total di titik COMMIT dengan pesan error yang benar.
* **Seed Chart of Accounts wajib** sebelum modul ini bisa jalan: `1-1000 Kas` (asset/debit), `2-1001/1002/1003 Simpanan Pokok/Wajib/Sukarela Anggota` (liability/credit) — via `ChartOfAccountsSeeder`, dipanggil dari `DatabaseSeeder` dan dari `tests/Pest.php` (`beforeEach` khusus folder `Feature/Savings`).
* **Immutability guard** (rules/models.md §5) diterapkan ke `JournalEntry`, `JournalLine`, dan `SavingsTransaction` — update/delete melempar `FinancialImmutableException` yang dipetakan ke HTTP 409 di `bootstrap/app.php`. `InsufficientBalanceException` dipetakan ke HTTP 400.
* **Idempotency middleware baru** (`App\Http\Middleware\EnsureIdempotencyKey`, alias `idempotency`) sesuai `rules/api.md` §4 & `rules/security.md` §3: pakai Redis (`Cache::add` sebagai lock, `Cache::put` untuk cache respons 24 jam) — 400 jika header `X-Idempotency-Key` tidak ada, 409 jika key yang sama sedang diproses, dan respons tersimpan (bukan eksekusi ulang) jika key sama dipakai lagi dalam 24 jam. Hanya dipasang di `POST /savings/deposit` & `POST /savings/withdraw`. Sengaja **tidak** mem-cache respons gagal (validasi/saldo kurang) supaya percobaan ulang dengan data benar tidak terblokir permanen.
* **Alur aktivasi keanggotaan** (`App\Actions\Members\ActivateMembershipAction`, dipanggil dari `DepositSavingsAction` setiap kali ada setoran ke Simpanan Pokok): anggota baru AKTIF (dapat `member_number` format `KOP-YYYY-XXXXX` + `joined_at`) hanya jika **KYC `approved` DAN** saldo Pokok ≥ `config('koperasi.simpanan_pokok_amount')`. Karena endpoint approval KYC pengurus belum dibangun (lihat catatan Sprint 1), aktivasi otomatis ini baru akan benar-benar terpicu setelah backoffice itu ada — sudah diuji manual lewat tinker (buat record KYC approved langsung) dan lewat Pest.
* **3 rekening simpanan (Pokok/Wajib/Sukarela) otomatis dibuat saat registrasi** (`CreateMemberSavingsAccountsAction`, dipanggil dari `RegisterMemberAction`), saldo nol, nomor rekening `{PREFIX}-{10 karakter acak}`.
* **Endpoint baru** (`App\Http\Controllers\Api\V1\Savings`): `GET /api/v1/savings` (ringkasan 3 saldo), `POST /api/v1/savings/deposit` (butuh `X-Idempotency-Key`; "simulasi" — saldo & jurnal langsung ter-update begitu request sukses, tidak ada integrasi VA/payment gateway sungguhan), `POST /api/v1/savings/withdraw` (khusus tipe sukarela, butuh `X-Idempotency-Key`).
* **Testing**: 11 test Pest baru di `tests/Feature/Savings/` (ringkasan, deposit + idempotency + aktivasi, withdraw + saldo kurang) — total 23 test lulus. Diverifikasi manual end-to-end dulu via `curl` + `php artisan queue:work --once` + tinker (termasuk sengaja memicu entry jurnal tak seimbang untuk membuktikan trigger DEFERRED bekerja) sebelum dikunci jadi test otomatis.
* **Belum dikerjakan**: endpoint approval/reject KYC pengurus (prasyarat agar aktivasi otomatis benar-benar terpicu di alur nyata), integrasi Virtual Account/QRIS sungguhan (Midtrans/Xendit — saat ini "simulasi" sinkron), Simpanan Wajib bulanan otomatis (baru manual deposit), dan sisi Flutter (`design/manajemen_simpanan`) untuk Sprint 2.

### 5. Sprint 3: Layanan Pinjaman & Multi-Tier Approval
* **Catatan penting soal ERD.md**: diagram mermaid mereferensikan entitas `LOAN_APPLICATIONS` dan `LOAN_APPROVALS` (lewat relasi `MEMBERS ||--o{ LOAN_APPLICATIONS`, dst.) tapi **tidak pernah mendefinisikan kolomnya** — beda dengan `LOAN_PRODUCTS`/`LOANS`/`LOAN_INSTALLMENTS` yang lengkap. Skema kedua tabel itu (`loan_applications`, `loan_approvals`) dirancang sendiri berdasarkan konteks BRD.md §5.3 & contoh `ApplyLoanRequest` di `rules/controllers.md` — bukan disalin dari ERD karena memang tidak ada di sana. Kalau ERD.md pernah direvisi untuk menambahkan definisi resminya, sinkronkan skema migrasi terhadap itu.
* **Migrasi baru**: `loan_products`, `loan_applications`, `loan_approvals`, `loans`, `loan_installments` (UUID v7). `loan_products.annual_interest_rate` menyimpan suku bunga **tahunan** (sesuai nama kolom di ERD) walau formula BRD.md §5.3.2 memakai rate **bulanan** — konversi `÷12` dilakukan di `LoanProduct::monthlyInterestRate()`, bukan disimpan dua kali.
* **`LoanCalculationService`** (`app/Services/Loans/`) — dua metode sesuai BRD.md §5.3.2, dites persis sesuai contoh di `rules/testing.md`:
  * **Flat**: pokok & bunga per bulan konstan dari plafon awal.
  * **Sliding/menurun**: pokok per bulan konstan (sama seperti flat), tapi bunga dihitung dari sisa pokok bulan sebelumnya → menurun tiap bulan (BUKAN skema anuitas Barat dengan angsuran tetap — BRD.md hanya memberi rumus bunga, bukan rumus anuitas, jadi dipilih interpretasi paling langsung sesuai rumus yang ada).
  * Pembulatan 2 desimal per baris; sisa pembulatan pokok diserap di angsuran terakhir supaya total pokok jadwal = plafon persis.
* **Plafon & DSR** (`LoanEligibilityService`): plafon maks = `3 × total saldo simpanan` (Pokok+Wajib+Sukarela), DSR = angsuran bulanan tertinggi dalam jadwal ≤ `35% × gaji_pokok_bulanan` (`members.monthly_salary`, sudah ada sejak Sprint 1). Untuk sliding, dipakai angsuran **tertinggi** (bulan pertama) sebagai uji DSR — paling konservatif.
* **Persetujuan berjenjang** (`LoanApprovalTierService` + `config('koperasi.loan.approval_tiers')`): 3 jenjang nominal dari BRD.md §5.3.3, tapi peran "Sekretaris"/"Analis" di BRD **tidak ada** di `App\Enums\UserRole` (cuma member/treasurer/chairman/auditor/superadmin) — dipetakan ulang: ≤Rp5jt = 1 approval (treasurer/chairman/superadmin), Rp5jt–25jt = 2 approval (treasurer/chairman/superadmin), >Rp25jt = 2 approval **khusus** chairman/superadmin. Satu approver hanya boleh memutuskan sekali per pengajuan (unique constraint `loan_application_id`+`approver_id`); satu penolakan langsung menolak seluruh pengajuan (veto tunggal).
* **Disbursement otomatis**: begitu jumlah approval tier terpenuhi, `DecideLoanApplicationAction` langsung memanggil `DisburseLoanAction` (bikin `Loan` + seluruh `loan_installments` dari hasil kalkulasi + dispatch `LoanDisbursedEvent`) — tidak ada langkah pencairan manual terpisah, sesuai SLA BRD "<4 Jam (Otomasi Plafon)".
* **Akuntansi**: 2 akun baru di `ChartOfAccountsSeeder` — `1-1200 Piutang Pinjaman Anggota` (asset) & `4-1000 Pendapatan Bunga Pinjaman` (revenue). `JournalPostingService` di-refactor jadi method generik `postEntry(lines[])` (bukan cuma 2 baris tetap) supaya bisa menangani jurnal pembayaran angsuran yang 3 baris (Debit Kas total; Kredit Piutang Pinjaman porsi pokok; Kredit Pendapatan Bunga porsi bunga) — trigger DEFERRED tetap valid karena cuma mengecek SUM(debit)=SUM(credit), tidak peduli jumlah baris.
* **Endpoint baru** (`App\Http\Controllers\Api\V1\Loans`): `GET /loans/simulate`, `POST /loans/apply` (idempotency), `GET /loans`, `POST /loans/{loan}/repay` (idempotency — melunasi angsuran jatuh tempo berikutnya secara penuh, simulasi, belum ada payroll-cut/VA sungguhan). ~~`POST /loans/applications/{id}/decide` sementara lewat Sanctum~~ — **sudah dipindah ke portal Web Admin** di sesi berikutnya (lihat §7).
* **`config/koperasi.php`** diperluas dengan section `loan` (ceiling_multiplier, dsr_max_ratio, approval_tiers) — semua angka kebijakan pinjaman terpusat, tidak hardcoded di Action.
* **Testing**: unit test `LoanCalculationServiceTest` mengunci contoh persis dari `rules/testing.md` (flat 12jt/12bln/0.8% → angsuran 1.096.000, total 13.152.000), + 15 test Pest baru di `tests/Feature/Loans/` (simulate, apply+plafon+DSR, decide multi-tier+reject+forbidden, repay+idempotency+paid_off) — total 42 test lulus. Diverifikasi juga end-to-end manual via curl + queue worker (register → aktivasi via tinker → simulate flat & sliding → apply ditolak karena plafon → apply sukses → treasurer approve (tier-2, 1/2) → chairman approve (2/2, trigger disbursement) → cek jurnal pencairan balance → repay angsuran → cek jurnal 3-baris balance → reject-path tier-1).
* **Belum dikerjakan**: portal Web Admin/backoffice sungguhan untuk approval pengurus (lihat catatan di controller — **sudah dikerjakan, lihat §7**), payroll-cut/VA otomatis untuk angsuran, penanganan `loan_installments.status = overdue` (belum ada job terjadwal yang menandai keterlambatan), dan sisi Flutter (`design/layanan_pinjaman`) untuk Sprint 3.

### 6. Sinkronisasi ERD.md
* `ERD.md` (dan salinannya `docs/ERD.md`) direvisi menyeluruh supaya cocok 1:1 dengan migrasi yang sudah ada: `loan_applications` & `loan_approvals` yang sebelumnya cuma direferensikan di diagram (tanpa definisi kolom) sekarang dilengkapi; `users.password_hash` → `password` + `name`; `members.nik UNIQUE` (yang sebenarnya tidak bisa diterapkan pada kolom terenkripsi) diganti jadi `nik` (terenkripsi) + `nik_hash CHAR(64) UNIQUE` dengan catatan penjelasan; `member_number` jadi nullable; ditambahkan constraint `UNIQUE(member_id, type)` di `savings_accounts`; timestamp yang sebelumnya hilang di beberapa tabel (`chart_of_accounts`, `loan_products`, dll.) dilengkapi. Commit terpisah dari fitur portal admin supaya mudah ditelusuri.

### 7. Portal Web Admin (Backoffice Pengurus & Super Admin)
* **Channel terpisah sungguhan** sesuai AGENTS.md §1 & rules/controllers.md: sesi (`guard: web`, cookie `HttpOnly` + `SameSite=Strict`, `SESSION_LIFETIME=30` menit sesuai rules/security.md §1), bukan token Sanctum. Middleware baru `EnsureAdminRole` (alias `admin.role`) menolak role `member` dengan 403 — anggota mutlak hanya lewat mobile.
* **Endpoint API lama yang tidak sesuai channel dihapus, bukan dibiarkan dobel**: `POST /api/v1/loans/applications/{id}/decide` (controller, request, route, test) dihapus total dan digantikan endpoint Web Admin — sesuai catatan TODO eksplisit yang ditinggalkan di kode saat Sprint 3. KYC approval sebelumnya memang belum pernah dibangun sama sekali (bukan pindahan), jadi murni fitur baru.
* **Bug nyata yang ketemu & diperbaiki saat uji manual**: middleware `Authenticate` bawaan Laravel me-redirect guest ke route bernama literal `login` — karena rute kita bernama `admin.login`, akses `/admin` tanpa sesi menghasilkan **500** (`RouteNotFoundException`), bukan redirect rapi. Diperbaiki dengan `$middleware->redirectGuestsTo(fn () => route('admin.login'))` di `bootstrap/app.php`. Juga ditemukan: exception handler `render()` untuk `InsufficientBalanceException`/`FinancialImmutableException`/`LoanWorkflowException` (dari Sprint 2-3) selalu mengembalikan JSON tanpa syarat — kalau dipanggil dari request Web Admin (bukan API), pengguna akan melihat JSON mentah alih-alih redirect-dengan-pesan-error. Diperbaiki jadi channel-aware (`$request->is('api/*') ? JSON : back()->withErrors(...)`).
* **Fitur**: dasbor ringkasan (jumlah KYC & pengajuan pinjaman menunggu), verifikasi KYC (lihat foto KTP/selfie via presigned URL 5 menit, approve/reject dengan alasan wajib — approve otomatis memicu ulang `ActivateMembershipAction` sehingga menutup celah "Pokok sudah lunas duluan sebelum KYC disetujui" dari Sprint 2), dan panel persetujuan pinjaman berjenjang (reuse penuh `DecideLoanApplicationAction` dari Sprint 3 — tidak ada logika bisnis yang diduplikasi, controller Web cuma pembungkus tipis).
* **UI**: Blade + Tailwind CSS v4 (sudah terpasang di `package.json`/`vite.config.js` sejak skeleton awal tapi belum pernah di-`npm install`/build — sekarang sudah). Token warna Sapphire/Cobalt & font Plus Jakarta Sans/Inter dari `rules/design.md` dipakai untuk konsistensi merek, TAPI aturan "kepatuhan 1:1 desain" di rules/design.md eksplisit hanya berlaku untuk Flutter — jadi tidak ada mockup `design/` yang wajib diikuti persis untuk portal ini.
* **Testing**: 18 test Pest baru di `tests/Feature/Admin/` (login/logout/role-gating, approve/reject KYC + trigger aktivasi, decide pinjaman tier-1/tier-2/reject/duplikat/salah-jenjang) — total 57 test lulus. Diverifikasi juga end-to-end manual dengan curl asli (cookie jar + ekstraksi token CSRF dari halaman, bukan cuma lewat Pest) untuk memastikan alur sungguhan (bukan cuma test-client shortcut) benar-benar jalan — di sinilah dua bug di atas ketemu.
* **Belum dikerjakan**: 2FA TOTP untuk Super Admin & persetujuan pencairan besar (rules/security.md §1 — fitur terpisah yang cukup besar, sengaja belum digarap), audit log (`AUDIT_LOGS` cuma relasi di ERD, belum ada tabel/implementasi).

### 8. Halaman Manajemen Anggota (Portal Web Admin)
* Ketika ditanya item mana dari daftar "belum dikerjakan" Sprint 7 yang mau dikerjakan lanjut, pengguna memilih "Halaman backoffice lain" (bukan 2FA atau audit log — keduanya masih terbuka).
* **Fitur** (`App\Http\Controllers\Web\Admin\Members\MemberController`): `GET /admin/members` — daftar anggota dengan pencarian (nama/NIP/nomor anggota, pakai `ILIKE` khusus Postgres) & filter status; `GET /admin/members/{member}` — detail profil, saldo 3 simpanan, riwayat pinjaman, dan status KYC (dengan tautan langsung ke halaman tinjau KYC bila masih pending).
* **NIK ditampilkan tersamar** (`Member::maskedNik()`, format `3201 0420 •••• 0005` sesuai `rules/formatting.md` §3.2) — bukan NIK penuh, walau field `nik` itu sendiri bisa diakses via accessor PHP (atribut `#[Hidden]` di model cuma menyembunyikan dari serialisasi array/JSON, bukan dari pemanggilan properti langsung di Blade).
* `MemberStatus` diberi method `label()` (menyusul pola yang sama seperti `SavingsType::label()`) untuk label Indonesia di badge status.
* **Testing**: 5 test Pest baru di `tests/Feature/Admin/MemberManagementTest.php` (list, search, filter status, detail lengkap, role member ditolak) — total 62 test lulus. Diverifikasi juga manual via curl+cookie jar.
* **Belum dikerjakan**: aksi ubah status anggota (suspend/resign) sengaja tidak dibuat di halaman ini — BRD.md §5.1 mensyaratkan proses pengunduran diri melibatkan pelunasan pinjaman & pengembalian simpanan, itu alur bisnis terpisah yang belum dirancang, jadi halaman ini read-only dulu.
