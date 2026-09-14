# Dokumentasi Sistem Koperasi Digital (*KopKar Digital*)

Selamat datang di repositori dokumentasi arsitektur dan spesifikasi resmi sistem **Koperasi Digital**. Sistem ini dirancang menggunakan arsitektur enterprise modern dengan **Backend Laravel 13**, **Frontend Mobile Flutter**, dan **Database PostgreSQL 16+**.

---

## 1. Dokumen Inti Proyek (*Core Specifications*)

1. **[Product Requirements Document (PRD.md)](file:///Users/yapi/Adi/appdev/koperasi-digital/PRD.md)**:
   * Spesifikasi lengkap produk, profil persona pengguna (anggota, bendahara, pengawas), modul fitur fungsional (simpanan, pinjaman, kopmart, QRIS, RAT e-voting), dan kebutuhan non-fungsional.
2. **[Business Requirements Document (BRD.md)](file:///Users/yapi/Adi/appdev/koperasi-digital/BRD.md)**:
   * Batasan bisnis, regulasi UU Perkoperasian No. 25/1992, UU P2SK, Permenkop UKM No. 8/2023, aturan plafon pinjaman, rumus pembagian SHU tahunan, dan mitigasi risiko NPL.
3. **[Entity Relationship Diagram (ERD.md)](file:///Users/yapi/Adi/appdev/koperasi-digital/ERD.md)**:
   * Diagram relasi entitas Mermaid, skema relasional PostgreSQL, kamus data lengkap, konstrain saldo non-negatif, dan trigger penyeimbang pembukuan berpasangan (*double-entry balancing*).
4. **[Technology Stack Specification (techstack.md)](file:///Users/yapi/Adi/appdev/koperasi-digital/techstack.md)**:
   * Rincian dependensi Laravel 13, Flutter 3.x, PostgreSQL 16+, Redis 7, MinIO/S3, integrasi payment gateway QRIS, dan CI/CD.
5. **[System Architecture & Technical Design (arsitektur.md)](file:///Users/yapi/Adi/appdev/koperasi-digital/arsitektur.md)**:
   * Diagram kontainer C4, Clean Architecture backend & mobile, siklus transaksi finansial dengan idempotency key, dan *state machine* persetujuan pinjaman.

---

## 2. Pedoman & Aturan Rekayasa (*Engineering Rules*)

Seluruh 17 dokumen aturan standar pengembangan tersimpan di folder [`rules/`](file:///Users/yapi/Adi/appdev/koperasi-digital/rules/):
* `rules/overview.md` — Filosofi & prinsip arsitektur sistem.
* `rules/design.md` — Standar desain UI/UX Flutter (warna, font, elevasi).
* `rules/components.md` — Spesifikasi komponen widget reusable Flutter.
* `rules/transalations.md` — Glosarium istilah baku perkoperasian Indonesia.
* `rules/formatting.md` — Standar format Rupiah, tanggal WIB, dan NIK.
* `rules/controllers.md` — Standar Controller Laravel 13 (Invokable & Form Request).
* `rules/models.md` — Standar Eloquent Model (UUID, Casts, Immutability).
* `rules/database.md` — Standar PostgreSQL (Decimal 15,2, locking, indexing).
* `rules/views.md` — Standar presentasi layar mobile Flutter (4 UI states).
* `rules/api.md` — Standar RESTful API & Idempotency Key.
* `rules/code-style.md` — Standar kode (Pint, Effective Dart, Conventional Commits).
* `rules/security.md` — Standar keamanan fintech (PIN, enkripsi PII, audit log).
* `rules/testing.md` — Strategi pengujian (Pest PHP & Flutter Test).
* `rules/development-plan.md` — Roadmap fase pengembangan & Git Flow.
* `rules/flutter-app.md` — Blueprint arsitektur mobile Flutter.
* `rules/dispatch-engine.md` — Engine transaksi event-driven & Laravel Horizon.
* `rules/depleyment.md` — Panduan deployment Docker Compose & CI/CD.

---

## 3. Aset UI/UX Desain Flutter (`design/`)
Seluruh prototipe visual dan kode mockup antarmuka mobile Flutter tersimpan di folder [`design/`](file:///Users/yapi/Adi/appdev/koperasi-digital/design/).
