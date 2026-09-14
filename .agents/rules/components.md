# Aturan Pengembangan: Komponen UI (*Components*) Flutter

Dokumen ini merumuskan spesifikasi komponen UI modular yang dapat digunakan kembali (*reusable widgets*) pada aplikasi Flutter Koperasi Digital. Setiap komponen harus patuh pada `rules/design.md`.

---

## 1. Komponen Kartu Finansial (*Financial Cards*)

### `FinancialHeroCard`
Kartu saldo utama di Beranda Anggota yang menampilkan total aset, tombol sembunyikan/tampilkan saldo (*privacy toggle*), dan rincian simpanan.
* **Properti**:
  * `totalBalance`: `double` (Total akumulasi Simpanan Pokok + Wajib + Sukarela).
  * `pokokBalance`, `wajibBalance`, `sukarelaBalance`: `double`.
  * `isBalanceVisible`: `bool` (State untuk mask teks: `Rp ••••••••`).
  * `onTopUp`: `VoidCallback` (Arahkan ke pembayaran/setoran).
  * `onWithdraw`: `VoidCallback` (Arahkan ke penarikan sukarela).
* **Styling**:
  * Latar belakang: Gradien biru `LinearGradient(colors: [Color(0xFF1D4ED8), Color(0xFF1E40AF)], begin: Alignment.topLeft, end: Alignment.bottomRight)`.
  * Radius: 20px (`BorderRadius.circular(20)`).
  * Angka saldo: `AppTypography.financialHero` (Inter 32px Bold).

### `FinancialMetricCard`
Kartu ringkas untuk menampilkan metrik spesifik (misal: Sisa Plafon Pinjaman, Estimasi SHU Berjalan, Tagihan Bulan Ini).
* **Properti**: `title` (`String`), `amount` (`double`), `subtitle` (`String?`), `icon` (`IconData`), `statusColor` (`Color`).
* **Styling**: Latar putih, border 1px `outline`, padding 16px, radius 16px.

---

## 2. Komponen Status & Badge (*Badges & Chips*)

### `AppStatusBadge`
Badge indikator status transaksi atau permohonan pinjaman.
```dart
enum CooperativeStatus {
  active,     // Hijau: Keanggotaan aktif, transaksi berhasil
  pending,    // Amber/Kuning: Menunggu verifikasi pengurus/KYC
  inReview,   // Biru: Sedang diverifikasi komite kredit
  approved,   // Emerald: Pinjaman disetujui, siap cair
  rejected,   // Merah: Ditolak
  paidOff,    // Biru tua/Hijau: Lunas
  overdue,    // Merah menyala: Jatuh tempo / menunggak
}
```
* **Styling**:
  * Padding: `EdgeInsets.symmetric(horizontal: 10, vertical: 4)`.
  * Bentuk: `StadiumBorder()` (`BorderRadius.circular(9999)`).
  * Tipografi: `AppTypography.labelSmall` (Inter 11px SemiBold).

---

## 3. Komponen Form & Input (*Input Fields*)

### `RupiahTextField`
Input teks khusus nominal uang Rupiah dengan format desimal otomatis dan pemisah ribuan.
* **Aturan Format**:
  * Otomatis memformat angka menjadi `Rp 1.500.000` saat pengguna mengetik.
  * Hanya menerima masukan angka (*numeric keyboard*).
  * Menyimpan nilai integer/double murni ke state controller tanpa karakter non-angka.
* **Tampilan**:
  * Ketinggian: 48px - 52px.
  * Border: `OutlineInputBorder` dengan radius 12px.
  * Focus state: Border warna `AppColors.secondary` dengan ketebalan 1.5px.

### `AppDropdownField<T>`
Pilihan dropdown seragam untuk jenis simpanan, tenor pinjaman (bulan), atau kategori transaksi.

---

## 4. Komponen Tombol Tindakan (*Action Buttons*)

### `PrimaryButton`
Tombol aksi utama (e.g., "Ajukan Pinjaman", "Konfirmasi Pembayaran", "Setor Saldo").
* **Ketinggian**: Minimal 48px (standar aksesibilitas sentuhan jari).
* **Latar**: `AppColors.primary` (`#1D4ED8`). Teks putih bold.
* **Loading State**: Menampilkan `SizedBox(height: 20, width: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))` dan mendisable interaksi pengguna ketika `isLoading == true`.

### `SecondaryButton` & `OutlineButton`
* `SecondaryButton`: Latar `AppColors.tertiary` (`#DBEAFE`) dengan teks biru `AppColors.primary`.
* `OutlineButton`: Latar putih dengan border 1px `AppColors.outline` dan teks hitam `AppColors.onSurface`.

---

## 5. Komponen Daftar Transaksi (*Transaction List Item*)

### `TransactionTile`
Item riwayat mutasi keuangan:
* **Sisi Kiri**: Avatar bulat berisi ikon kategori (Simpanan, Pinjaman, Kopmart, Tarik Dana).
* **Sisi Tengah**: Judul transaksi (e.g., "Setoran Simpanan Wajib"), nomor referensi, dan tanggal jam.
* **Sisi Kanan**: Nominal uang.
  * Pemasukan / Dana Masuk: Teks hijau `+ Rp 500.000` (`AppColors.success`).
  * Pengeluaran / Potongan: Teks gelap `- Rp 250.000` (`AppColors.onSurface`).

---

## 6. Komponen Navigasi Bawah (*Bottom Navigation Bar*)

### `CooperativeBottomBar`
Bilah navigasi utama dengan 5 tab:
1. **Beranda** (`Icons.home_rounded`)
2. **Simpanan** (`Icons.account_balance_wallet_rounded`)
3. **Pinjaman** (`Icons.payments_rounded`)
4. **Kopmart** (`Icons.storefront_rounded`)
5. **Akun** (`Icons.person_rounded`)
* **Styling**: Tinggi 72px, latar putih, elevasi Level 3, border atas 1px.
* **Indikator Aktif**: Ikon berwarna biru `AppColors.primary`, teks bold, serta titik dot kecil (4px) di bawah teks tab.

---

## 7. Modal & Bottom Sheet (*Dialogs & Sheets*)

### `ConfirmationBottomSheet`
Sheet geser dari bawah untuk mengonfirmasi transaksi penting (misal: transfer, bayar QRIS, konfirmasi pengajuan pinjaman):
* Menampilkan rincian: Nominal, Biaya Layanan, Total Potong Saldo, Rekening Tujuan.
* Tombol "Konfirmasi & Masukkan PIN" dan tombol "Batal".
* Drag handle di bagian atas tengah (lebar 36px, tinggi 4px, warna abu-abu netral).
