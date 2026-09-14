# Katalog & Panduan Desain UI/UX Flutter (`design/`)

> [!IMPORTANT]
> **ATURAN MUTLAK PENYESUAIAN DESAIN FLUTTER**:
> Seluruh antarmuka mobile Flutter **WAJIB MENYESUAIKAN PERSIS 1:1** dengan contoh desain yang diunggah di folder ini.
> Pengembang dan AI dilarang mengubah tata letak (*layout*), hierarki tombol, palet warna, tipografi, maupun bentuk kartu di luar contoh visual yang terdapat pada berkas `screen.png` dan berkas prototipe `code.html`.

Folder ini adalah tempat utama untuk menampung seluruh referensi desain antarmuka (*UI/UX designs*) aplikasi mobile Flutter Koperasi Digital. Setiap pengembang atau asisten AI yang membangun layar Flutter **wajib** merujuk pada visual dan kode markup di folder ini untuk menghasilkan tampilan yang presisi (*pixel-perfect*).

---

## 1. Katalog Layar yang Sudah Tersedia (*Existing Screens*)

Saat ini telah tersedia 8 layar mobile utama beserta dokumen panduan sistem desain:

| Folder Layar | Nama Modul | Berkas Tersedia | Padanan Fitur Flutter (`mobile/lib/features/`) |
| :--- | :--- | :--- | :--- |
| **`beranda_anggota/`** | Beranda & Dasbor Anggota | `screen.png`, `code.html` | `features/home/presentation/screens/home_screen.dart` |
| **`manajemen_simpanan/`** | Ringkasan Simpanan (Pokok, Wajib, Sukarela) | `screen.png`, `code.html` | `features/savings/presentation/screens/savings_screen.dart` |
| **`layanan_pinjaman/`** | Layanan & Kalkulator Pinjaman | `screen.png`, `code.html` | `features/loans/presentation/screens/loans_screen.dart` |
| **`kopmart_belanja_karyawan/`** | Belanja Kebutuhan Karyawan | `screen.png`, `code.html` | `features/kopmart/presentation/screens/kopmart_screen.dart` |
| **`bayar_via_qris/`** | Pemindai Kamera & Upload QRIS | `screen.png`, `code.html` | `features/qris/presentation/screens/qris_scanner_screen.dart` |
| **`konfirmasi_bayar_qris/`** | Lembar Konfirmasi & Input PIN | `screen.png`, `code.html` | `features/qris/presentation/screens/qris_confirm_screen.dart` |
| **`bukti_pembayaran_qris/`** | Kuitansi & Struk Transaksi Digital | `screen.png`, `code.html` | `features/qris/presentation/screens/qris_receipt_screen.dart` |
| **`riwayat_transaksi_mutasi/`** | Mutasi Transaksi & Filter Tanggal | `screen.png`, `code.html` | `features/history/presentation/screens/history_screen.dart` |
| **`kopkar_digital/`** | **Design System Tokens** | `DESIGN.md` | `core/theme/` & `shared/constants/colors.dart` |

---

## 2. Panduan Mengunggah Desain Baru (*Upload Instructions*)

Jika Anda ingin menambahkan desain layar baru (misal: layar E-Voting RAT, detail profil anggota, form ganti PIN, atau layar login):

1. **Buat Sub-folder Baru di dalam `design/`**:
   * Gunakan penamaan `snake_case` berbahasa Indonesia yang mencerminkan fungsi layar.
   * Contoh:
     * `design/e_voting_rat/`
     * `design/profil_anggota/`
     * `design/login_dan_registrasi/`
     * `design/form_kyc_ktp/`
2. **Format Berkas yang Didukung**:
   * **Gambar Visual**: `screen.png` atau `screen.jpg` (tangkapan layar resolusi tinggi dari Figma/Adobe XD).
   * **Struktur / HTML Mockup**: `code.html` (jika mengekspor kode prototipe dari Figma / Web).
   * **Catatan / Spesifikasi**: `README.md` (opsional, jika ada interaksi khusus seperti animasi atau validasi mikro).
3. **Konvensi Tipografi & Warna**:
   * Pastikan desain merujuk ke token yang telah didefinisikan di [`design/kopkar_digital/DESIGN.md`](file:///Users/yapi/Adi/appdev/koperasi-digital/design/kopkar_digital/DESIGN.md) dan [`rules/design.md`](file:///Users/yapi/Adi/appdev/koperasi-digital/rules/design.md):
     * Warna Utama: Sapphire Blue (`#1D4ED8`) & Electric Cobalt (`#2563EB`).
     * Tipografi: **Plus Jakarta Sans** untuk judul dan **Inter** untuk data finansial Rupiah.
     * Grid & Radius: 8-point base grid, `rounded-2xl` (16px) untuk kartu, `rounded-xl` (12px) untuk tombol dan input.

---

## 3. Pemetaan ke Komponen Widget Flutter

Saat mengonversi desain di folder ini ke kode Dart Flutter:
* Warna $\rightarrow$ gunakan `AppColors` di `shared/constants/app_colors.dart`.
* Teks & Judul $\rightarrow$ gunakan `AppTypography` di `shared/constants/app_typography.dart`.
* Format Mata Uang $\rightarrow$ gunakan `CurrencyFormatter.format(amount)`.
* Jarak Navigasi Bawah $\rightarrow$ tambahkan `EdgeInsets.only(bottom: 96)` pada scroll view utama agar elemen tidak terpotong bottom navigation bar.
