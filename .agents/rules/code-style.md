# Aturan Pengembangan: Standar Gaya Kode (*Code Style*)

Dokumen ini merumuskan standar penulisan kode sumber untuk backend Laravel 13 dan aplikasi mobile Flutter Koperasi Digital untuk memastikan kode bersih, terstandarisasi, dan mudah dirawat.

---

## 1. Standar Kode Backend (PHP / Laravel 13)

1. **Gaya Kode & Linter**:
   * Mematuhi standar **PSR-12** dan standar resmi Laravel via **Laravel Pint**.
   * Jalankan `vendor/bin/pint` sebelum melakukan *commit* atau *pull request*.
2. **Kepatuhan Strict Types**:
   * Setiap berkas PHP **wajib** menyertakan deklarasi di baris pertama:
     ```php
     declare(strict_types=1);
     ```
3. **Konvensi Penamaan (Naming Conventions)**:
   * **Class**: `PascalCase` (e.g., `LoanCalculationService`, `MemberRepository`).
   * **Method & Variabel**: `camelCase` (e.g., `calculateMonthlyInstallment()`, `$currentBalance`).
   * **Konstanta**: `SCREAMING_SNAKE_CASE` (e.g., `MAX_LOAN_MULTIPLIER`).
   * **Tabel & Kolom Database**: `snake_case` jamak untuk tabel (`savings_accounts`), tunggal untuk kolom (`member_id`, `created_at`).
4. **Pola Desain Action / Service**:
   * Tempatkan logika bisnis pada kelas **Action** dengan metode tunggal `execute()`:
     ```php
     namespace App\Actions\Savings;

     final readonly class DepositSavingsAction
     {
         public function __construct(
             private JournalPostingService $journalService,
         ) {}

         public function execute(Member $member, float $amount, SavingsType $type): Transaction
         {
             // Logika deposit atomik
         }
     }
     ```

---

## 2. Standar Kode Frontend (Dart / Flutter)

1. **Gaya Kode & Linter**:
   * Mengikuti pedoman resmi **Effective Dart** dan paket linter `flutter_lints`.
   * Jalankan `flutter analyze` untuk memastikan 0 peringatan (*zero warnings*).
2. **Konvensi Penamaan**:
   * **File & Direktori**: `snake_case` (e.g., `savings_overview_screen.dart`, `financial_hero_card.dart`).
   * **Class & Widget**: `PascalCase` (e.g., `SavingsAccountCard`, `LoanInstallmentNotifier`).
   * **Method & Variabel**: `camelCase` (e.g., `fetchTransactions()`, `isLoading`).
   * **Konstanta**: `lowerCamelCase` untuk konstanta lokal, `PascalCase` untuk Enum.
3. **Optimasi Widget Tree & `const` Constructors**:
   * Selalu gunakan kata kunci `const` pada widget statis untuk mencegah *re-render* yang tidak perlu:
     ```dart
     const SizedBox(height: 16);
     const Text('Plafon Pinjaman', style: AppTypography.titleMedium);
     ```
4. **Aturan Panjang Berkas (*File Length Limit*)**:
   * Satu berkas widget tidak boleh melebihi **250 baris**. Jika mulai panjang, pisahkan bagian komponen (misal: header kartu, list item, modal input) ke dalam berkas terpisah di sub-folder `widgets/`.

---

## 3. Konvensi Commit Git (*Conventional Commits*)

Setiap pesan commit wajib mengikuti format terstandarisasi:
```
<type>(<scope>): <deskripsi singkat>

[opsional body]
[opsional footer]
```

### Jenis Tipe Commit:
* `feat`: Fitur baru (e.g., `feat(loan): add sliding interest calculation engine`).
* `fix`: Perbaikan bug (e.g., `fix(auth): resolve token refresh loop on mobile`).
* `refactor`: Perubahan kode tanpa mengubah fungsi (e.g., `refactor(savings): extract journal posting into action`).
* `docs`: Pembaruan dokumentasi (e.g., `docs(erd): update journal_lines schema`).
* `test`: Penambahan atau perbaikan unit test (e.g., `test(loan): add test case for maximum ceiling limit`).
* `chore`: Tugas pemeliharaan rutin atau konfigurasi build/ci (e.g., `chore(deps): update flutter dependencies`).
