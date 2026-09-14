# Pedoman Proyek: Koperasi Digital (KopKar Digital)

Selamat datang di repositori **Koperasi Digital**. Repositori ini menerapkan standar rekayasa perangkat lunak enterprise untuk mendigitalkan ekosistem koperasi karyawan di Indonesia.

---

## 1. Tumpukan Teknologi & Pembagian Kanal Akses (*Primary Tech Stack & Roles*)
* **Web Backoffice (Super Admin & Pengurus)**: **Laravel 13** (PHP 8.3+ / 8.4, Blade / Inertia, Session Auth, CSRF, Laravel Horizon, Pest PHP) — *Diakses khusus via Web Browser desktop/laptop untuk manajemen anggota, approval pinjaman, dan akuntansi.*
* **Frontend Mobile (Anggota Koperasi)**: **Flutter 3.x** (Dart 3.x, Feature-First Clean Architecture, Flutter BLoC, Dio, GoRouter) — *Diakses khusus oleh Anggota Koperasi (karyawan) via smartphone Android & iOS.*
* **Backend REST API Core**: **Laravel 13** (Sanctum Tokens, Idempotency Engine, Event-Driven Dispatcher).
* **Database**: **PostgreSQL 16+** (Strict Foreign Keys, Check Constraints, Decimal(15,2), Pessimistic Row Locking).
* **Cache & Queues**: **Redis 7+** (Horizon queue worker & distributed locks).
* **Object Storage**: **MinIO / AWS S3** (Penyimpanan privat untuk berkas KYC KTP & bukti transfer).
* **UI/UX Referensi**: Folder **`design/`** (layar mobile lengkap: beranda, simpanan, pinjaman, kopmart, QRIS).

---

## 2. Peta Aturan Rekayasa (*Engineering Rules Catalog*)

Seluruh pengembang dan asisten AI wajib mematuhi 17 dokumen aturan spesifik di folder [`rules/`](file:///Users/yapi/Adi/appdev/koperasi-digital/rules/):

1. [`rules/overview.md`](file:///Users/yapi/Adi/appdev/koperasi-digital/rules/overview.md) — Filosofi sistem, domain bisnis, peran pengguna & kepatuhan UU Perkoperasian No. 25/1992.
2. [`rules/design.md`](file:///Users/yapi/Adi/appdev/koperasi-digital/rules/design.md) — Design system Flutter: token warna Sapphire & Cobalt, tipografi Plus Jakarta Sans & Inter, grid 8pt.
3. [`rules/components.md`](file:///Users/yapi/Adi/appdev/koperasi-digital/rules/components.md) — Reusable widgets Flutter: FinancialHeroCard, StatusBadge, RupiahTextField, BottomBar.
4. [`rules/transalations.md`](file:///Users/yapi/Adi/appdev/koperasi-digital/rules/transalations.md) — Glosarium istilah baku perkoperasian (Simpanan Pokok/Wajib/Sukarela, SHU, RAT, Kuorum, dll.).
5. [`rules/formatting.md`](file:///Users/yapi/Adi/appdev/koperasi-digital/rules/formatting.md) — Standar format mata uang Rupiah (`Rp 1.500.000`), tanggal WIB, NIK, dan persentase bunga.
6. [`rules/controllers.md`](file:///Users/yapi/Adi/appdev/koperasi-digital/rules/controllers.md) — Standar controller Laravel 13: Thin Controller, Invokable actions, Form Request & API Resources.
7. [`rules/models.md`](file:///Users/yapi/Adi/appdev/koperasi-digital/rules/models.md) — Standar Eloquent Model: UUID v7, PHP Enums, type casting, dan imutabilitas jurnal.
8. [`rules/database.md`](file:///Users/yapi/Adi/appdev/koperasi-digital/rules/database.md) — Standar PostgreSQL: Decimal(15,2), row locking `lockForUpdate()`, dan double-entry constraints.
9. [`rules/views.md`](file:///Users/yapi/Adi/appdev/koperasi-digital/rules/views.md) — Standar tampilan presentasi Flutter (4 UI states: Loading, Success, Empty, Error) & Backoffice web.
10. [`rules/api.md`](file:///Users/yapi/Adi/appdev/koperasi-digital/rules/api.md) — Standar RESTful API: Envelope response, endpoint map, versioning, dan header `X-Idempotency-Key`.
11. [`rules/code-style.md`](file:///Users/yapi/Adi/appdev/koperasi-digital/rules/code-style.md) — Konvensi gaya kode: PSR-12, Laravel Pint, Effective Dart, dan Conventional Commits.
12. [`rules/security.md`](file:///Users/yapi/Adi/appdev/koperasi-digital/rules/security.md) — Standar keamanan finansial: PIN 6-digit, enkripsi PII, presigned URL KYC, dan audit log mutlak.
13. [`rules/testing.md`](file:///Users/yapi/Adi/appdev/koperasi-digital/rules/testing.md) — Strategi pengujian: Pest PHP untuk kalkulasi bunga/SHU & Flutter widget/unit testing.
14. [`rules/development-plan.md`](file:///Users/yapi/Adi/appdev/koperasi-digital/rules/development-plan.md) — Roadmap sprint 1-6, strategi percabangan Git Flow, dan kriteria acceptance PR.
15. [`rules/flutter-app.md`](file:///Users/yapi/Adi/appdev/koperasi-digital/rules/flutter-app.md) — Blueprint arsitektur mobile Flutter: Feature-First Clean Architecture, Flutter BLoC, Dio, GoRouter.
16. [`rules/dispatch-engine.md`](file:///Users/yapi/Adi/appdev/koperasi-digital/rules/dispatch-engine.md) — Engine transaksi finansial: Event-driven architecture, Laravel Horizon, dan posting buku besar asinkron.
17. [`rules/depleyment.md`](file:///Users/yapi/Adi/appdev/koperasi-digital/rules/depleyment.md) — Panduan penerapan: Docker Compose, Nginx, PostgreSQL, Redis, CI/CD, dan build mobile release.

---

## 3. Dokumen Inti Proyek (*Core Documents*)
* [`PRD.md`](file:///Users/yapi/Adi/appdev/koperasi-digital/PRD.md) — Product Requirements Document (fitur, alur pengguna, kriteria penerimaan).
* [`BRD.md`](file:///Users/yapi/Adi/appdev/koperasi-digital/BRD.md) — Business Requirements Document (visi, regulasi, alur bisnis simpan pinjam, rumus SHU).
* [`ERD.md`](file:///Users/yapi/Adi/appdev/koperasi-digital/ERD.md) — Entity Relationship Diagram (skema lengkap database PostgreSQL & double-entry COA).
* [`techstack.md`](file:///Users/yapi/Adi/appdev/koperasi-digital/techstack.md) — Spesifikasi teknis terperinci backend, mobile, database, dan infrastruktur.
* [`arsitektur.md`](file:///Users/yapi/Adi/appdev/koperasi-digital/arsitektur.md) — Arsitektur sistem high-level, C4 diagram, dan alur transaksi.

---

## 4. Struktur Folder Desain UI/UX (`design/`) — Aturan Kepatuhan 1:1
Seluruh referensi layar UI/UX Flutter tersimpan di folder [`design/`](file:///Users/yapi/Adi/appdev/koperasi-digital/design/). 

> [!IMPORTANT]
> **ATURAN MUTLAK**: Setiap pembuatan layar atau komponen Flutter **WAJIB 100% MENYESUAIKAN PERSIS** dengan contoh desain yang telah diunggah di folder `design/`. Pengembang dan AI dilarang mengubah layout, ukuran kartu, palet warna, tipografi, maupun urutan tombol di luar contoh visual (`screen.png`) dan prototipe kode (`code.html`) yang telah disediakan pengguna.
