> [!IMPORTANT]
> **KEPATUHAN 1:1 TERHADAP CONTOH DESAIN DI FOLDER `design/`**:
> Seluruh implementasi antarmuka Flutter **WAJIB MENYESUAIKAN PERSIS 1:1** dengan contoh desain yang telah diunggah di folder [`design/`](file:///Users/yapi/Adi/appdev/koperasi-digital/design/).
> Pengembang dan asisten AI dilarang mengubah tata letak (*layout*), palet warna, tipografi, susunan kartu finansial, maupun hierarki tombol di luar referensi visual yang terdapat pada berkas `screen.png` dan berkas kode prototipe `code.html` di masing-masing subfolder desain.

---

## 1. Filosofi Visual & Identitas Merek
* **Gaya**: *Corporate Modern Fintech* yang terpercaya, transparan, dan ergonomis bagi anggota karyawan koperasi.
* **Karakter Visual**: Kombinasi warna biru laut (*Sapphire*) dan biru kobalt (*Cobalt Blue*) yang melambangkan stabilitas finansial, integritas institusi, serta kejelasan tata kelola.
* **Ergonomi**: Dirancang khusus untuk kemudahan navigasi satu tangan (*one-handed mobile interaction*), kejelasan pembacaan angka Rupiah, dan minim kelelahan visual (*cognitive fatigue*).
* **Rujukan Desain Wajib**:
  * `design/beranda_anggota` $\rightarrow$ Tampilan Layar Utama / Beranda Anggota.
  * `design/manajemen_simpanan` $\rightarrow$ Tampilan Manajemen Simpanan (Pokok, Wajib, Sukarela).
  * `design/layanan_pinjaman` $\rightarrow$ Tampilan Layanan Pinjaman & Kalkulator Simulasi Angsuran.
  * `design/kopmart_belanja_karyawan` $\rightarrow$ Tampilan Belanja Toko Koperasi Karyawan.
  * `design/bayar_via_qris` $\rightarrow$ Tampilan Pemindai Kamera & Upload Gambar QRIS.
  * `design/konfirmasi_bayar_qris` $\rightarrow$ Tampilan Lembar Konfirmasi Nominal & Input PIN.
  * `design/bukti_pembayaran_qris` $\rightarrow$ Tampilan Kuitansi & Bukti Struk Transaksi Digital.
  * `design/riwayat_transaksi_mutasi` $\rightarrow$ Tampilan Riwayat Transaksi & Filter Mutasi.
  * `design/kopkar_digital/DESIGN.md` $\rightarrow$ Spesifikasi Lengkap Token Warna, Tipografi & Spacing.

---

## 2. Palet Warna (*Color Tokens*)

### Palet Utama (*Core Tokens*)
```dart
class AppColors {
  // Primary - Sapphire Blue (Tombol utama, header card, brand icon)
  static const Color primary = Color(0xFF1D4ED8);
  static const Color onPrimary = Color(0xFFFFFFFF);
  static const Color primaryContainer = Color(0xFF1E40AF);
  static const Color onPrimaryContainer = Color(0xFFCAD3FF);

  // Secondary - Electric Cobalt (Aksen interaktif, badge aktif, toggle)
  static const Color secondary = Color(0xFF2563EB);
  static const Color onSecondary = Color(0xFFFFFFFF);
  static const Color secondaryContainer = Color(0xFF316BF3);

  // Tertiary - Ice Blue (Container badge, chip pasif, highlight)
  static const Color tertiary = Color(0xFFDBEAFE);
  static const Color onTertiary = Color(0xFF1E3A8A);

  // Canvas & Surfaces
  static const Color background = Color(0xFFF8FAFC); // Canvas layar belakang
  static const Color surface = Color(0xFFFFFFFF);    // Card utama & sheet
  static const Color surfaceVariant = Color(0xFFF1F5F9); // Field input latar
  static const Color onSurface = Color(0xFF0F172A);  // Teks judul gelap
  static const Color onSurfaceVariant = Color(0xFF475569); // Teks sekunder/label

  // Borders & Dividers
  static const Color outline = Color(0xFFE2E8F0);     // Garis tepi 1px card
  static const Color outlineFocus = Color(0xFF2563EB); // Garis input aktif

  // Status Finansial
  static const Color success = Color(0xFF059669);    // Lunas, Aktif, Berhasil
  static const Color successContainer = Color(0xFFD1FAE5);
  static const Color warning = Color(0xFFD97706);    // Menunggu Approval, Pending
  static const Color warningContainer = Color(0xFFFEF3C7);
  static const Color error = Color(0xFFDC2626);      // Ditolak, Macet, Gagal
  static const Color errorContainer = Color(0xFFFEE2E2);
}
```

---

## 3. Tipografi (*Typography Standards*)

Aplikasi menggunakan dua keluarga font utama:
1. **Plus Jakarta Sans**: Digunakan untuk judul (*Headlines*), nama layar, teks sambutan, dan label tindakan. Font ini ramah dan modern.
2. **Inter**: Digunakan untuk seluruh data numerik, nominal uang Rupiah (`Rp 15.000.000`), tanggal, persentase bunga, dan tabel. Font ini mendukung angka tabular (*tabular figures*) sehingga angka tersusun tegak lurus sempurna tanpa geser.

### Skala Tipografi Flutter
```dart
class AppTypography {
  // Plus Jakarta Sans - Judul & Display
  static const TextStyle displayLarge = TextStyle(
    fontFamily: 'PlusJakartaSans',
    fontSize: 28,
    fontWeight: FontWeight.w700,
    letterSpacing: -0.02,
    color: AppColors.onSurface,
  );

  static const TextStyle headlineLarge = TextStyle(
    fontFamily: 'PlusJakartaSans',
    fontSize: 22,
    fontWeight: FontWeight.w700,
    letterSpacing: -0.015,
    color: AppColors.onSurface,
  );

  static const TextStyle headlineMedium = TextStyle(
    fontFamily: 'PlusJakartaSans',
    fontSize: 18,
    fontWeight: FontWeight.w600,
    color: AppColors.onSurface,
  );

  static const TextStyle titleMedium = TextStyle(
    fontFamily: 'PlusJakartaSans',
    fontSize: 16,
    fontWeight: FontWeight.w600,
    color: AppColors.onSurface,
  );

  // Inter - Finansial & Data
  static const TextStyle financialBalance = TextStyle(
    fontFamily: 'Inter',
    fontSize: 26,
    fontWeight: FontWeight.w700,
    letterSpacing: -0.02,
    color: AppColors.onSurface,
  );

  static const TextStyle financialHero = TextStyle(
    fontFamily: 'Inter',
    fontSize: 32,
    fontWeight: FontWeight.w800,
    letterSpacing: -0.02,
    color: Colors.white,
  );

  static const TextStyle bodyMedium = TextStyle(
    fontFamily: 'PlusJakartaSans',
    fontSize: 14,
    fontWeight: FontWeight.w400,
    color: AppColors.onSurfaceVariant,
  );

  static const TextStyle labelSmall = TextStyle(
    fontFamily: 'Inter',
    fontSize: 12,
    fontWeight: FontWeight.w600,
    color: AppColors.onSurfaceVariant,
  );
}
```

---

## 4. Sistem Grid, Spacing & Bentuk (*Shapes*)

### Spacing (Kelipatan 4 & 8)
* `space-xxs`: 4px
* `space-xs`: 8px
* `space-sm`: 12px
* `space-md`: 16px (Standar margin horizontal mobile)
* `space-lg`: 20px
* `space-xl`: 24px
* `space-2xl`: 32px
* **Bottom Padding Clearance**: Seluruh layar yang memiliki *scroll view* wajib memberikan jarak bawah minimal **96px** agar tidak terpotong oleh bilah navigasi bawah (*Bottom Navigation Bar*).

### Radius Bentuk (*Border Radius*)
* **Hero Financial Card**: `BorderRadius.circular(20)`
* **Standard Card & Sheet**: `BorderRadius.circular(16)` (`rounded-2xl`)
* **Button & Text Field**: `BorderRadius.circular(12)` (`rounded-xl`)
* **Badges & Chips**: `BorderRadius.circular(9999)` (`rounded-full`)

---

## 5. Elevasi & Bayangan (*Shadows & Elevation*)

Hindari bayangan hitam pekat yang kotor. Gunakan ambient shadow bersemburat biru muda (*blue-tinted ambient shadows*):
* **Level 1 (Card Standar)**:
  `boxShadow: [BoxShadow(color: Color(0x0A0F172A), blurRadius: 4, offset: Offset(0, 2))]`
  Disertai garis tepi tipis `border: Border.all(color: AppColors.outline, width: 1.0)`.
* **Level 2 (Hero Financial Card / Floating CTA)**:
  `boxShadow: [BoxShadow(color: Color(0x141D4ED8), blurRadius: 16, offset: Offset(0, 6))]`.
* **Level 3 (Modal Bottom Sheet / Bottom Bar)**:
  `boxShadow: [BoxShadow(color: Color(0x0F0F172A), blurRadius: 20, offset: Offset(0, -4))]`.
