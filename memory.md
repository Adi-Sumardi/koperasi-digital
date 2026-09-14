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
