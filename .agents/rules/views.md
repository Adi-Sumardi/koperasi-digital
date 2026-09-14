# Aturan Pengembangan: Standar Tampilan & Presentasi (*Views*)

Dokumen ini merumuskan standar arsitektur lapisan presentasi (*presentation layer*) pada aplikasi mobile Flutter dan antarmuka panel administrasi web Koperasi Digital.

---

## 1. Standar Layar Mobile Flutter (*Mobile Screen Architecture*)

### Pemisahan Tampilan & Logika (*Separation of Concerns*)
* **Dilarang Menempatkan Logika Bisnis di Widget Tampilan**:
  * Widget `View` atau `Screen` murni bertanggung jawab merender UI berdasarkan state yang diberikan oleh state management (BLoC / Riverpod).
  * Seluruh kalkulasi bunga, validasi plafon, atau pemanggilan API wajib berada di layer *Domain Use Case* atau *Bloc/Notifier*.

### 4 Status Wajib Setiap Layar (*The 4 UI States*)
Setiap layar yang mengambil data dari API wajib mengimplementasikan 4 penanganan status tampilan:
1. **Loading State**: Gunakan animasi *Shimmer Skeleton* yang menyerupai bentuk kartu asli, bukan sekadar spinner bundar di tengah layar.
2. **Success / Loaded State**: Tampilan visual lengkap sesuai spesifikasi desain di folder `design/`.
3. **Empty State**: Tampilan ramah saat belum ada data (misal: "Belum ada riwayat transaksi", disertai ikon visual dan tombol aksi relevan).
4. **Error State**: Tampilan pesan kesalahan berbahasa Indonesia yang jelas, disertai tombol *"Coba Lagi"* (*retry mechanism*).

---

## 2. Struktur Dasar Layar (*Screen Template*)

```dart
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

class SavingsOverviewScreen extends StatelessWidget {
  const SavingsOverviewScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Manajemen Simpanan', style: AppTypography.headlineMedium),
        backgroundColor: Colors.white,
        elevation: 0,
        centerTitle: false,
      ),
      body: SafeArea(
        child: RefreshIndicator(
          onRefresh: () async {
            context.read<SavingsBloc>().add(FetchSavingsOverviewEvent());
          },
          child: BlocBuilder<SavingsBloc, SavingsState>(
            builder: (context, state) {
              if (state is SavingsLoadingState) {
                return const SavingsShimmerLoading();
              } else if (state is SavingsErrorState) {
                return AppErrorWidget(
                  message: state.message,
                  onRetry: () => context.read<SavingsBloc>().add(FetchSavingsOverviewEvent()),
                );
              } else if (state is SavingsLoadedState) {
                final data = state.overview;
                if (data.accounts.isEmpty) {
                  return const AppEmptyWidget(
                    title: 'Belum Ada Simpanan',
                    description: 'Selesaikan pembayaran simpanan pokok Anda.',
                  );
                }
                return SingleChildScrollView(
                  physics: const AlwaysScrollableScrollPhysics(),
                  padding: const EdgeInsets.only(
                    left: 16,
                    right: 16,
                    top: 16,
                    bottom: 96, // Jarak aman Bottom Bar
                  ),
                  child: Column(
                    children: [
                      FinancialHeroCard(account: data.primaryAccount),
                      const SizedBox(height: 16),
                      SavingsBreakdownList(items: data.accounts),
                    ],
                  ),
                );
              }
              return const SizedBox.shrink();
            },
          ),
        ),
      ),
    );
  }
}
```

---

## 3. Pencegahan Kesalahan Tampilan (*Overflow & Layout Guidelines*)

1. **Jarak Bebas Navigasi Bawah**:
   * Seluruh tampilan `ListView` atau `SingleChildScrollView` wajib menambahkan `padding: EdgeInsets.only(bottom: 96)` agar elemen terbawah tidak tertutup oleh bilah navigasi bawah (*Bottom Navigation Bar*).
2. **Penanganan Teks Panjang & Angka Rupiah**:
   * Gunakan `maxLines` dan `TextOverflow.ellipsis` pada label nama dan deskripsi.
   * Nilai nominal Rupiah tidak boleh terpotong titik tiga (`...`); jika layar sempit, sesuaikan ukuran font menggunakan `FittedBox(fit: BoxFit.scaleDown)` atau manfaatkan font tabular Inter.
3. **Adaptabilitas Tablet / Layar Lebar**:
   * Bungkus tata letak utama dengan kontainer terpusat (*centered container*) berlebar maksimal `maxWidth: 480` pada perangkat tablet untuk menjaga proporsi mobile yang ergonomis.

---

## 4. Standar Tampilan Panel Administrasi Web (Laravel Web Backoffice)

Panel **Web Laravel** dirancang khusus untuk **Super Admin** dan jajaran pengurus koperasi (Bendahara, Ketua, Pengawas, Operator Toko) dengan standar antarmuka web enterprise:

### 1. Struktur Layout & Navigasi
* **Sidebar Navigasi Tetap**: Menu terstruktur berdasarkan modul:
  * 📊 **Dasbor Eksekutif**: Metrik kas real-time, rasio NPL, total simpanan, permohonan pinjaman menunggu review.
  * 👥 **Manajemen Anggota**: Daftar anggota, detail profil, modal verifikasi & persetujuan KYC KTP.
  * 💰 **Layanan Simpanan**: Rekap simpanan pokok, wajib, sukarela, dan monitoring penarikan.
  * 📝 **Persetujuan Pinjaman**: Meja analisis kredit, kalkulator kelayakan, form persetujuan multi-level.
  * 🛒 **Kopmart Admin**: Manajemen stok barang, pemrosesan pesanan anggota, laporan omset belanja.
  * 🗳️ **Tata Kelola RAT**: Pembuatan agenda RAT, unggah berkas LPJ PDF, pemantauan kuorum & hasil voting live.
  * 📖 **Buku Besar & Akuntansi**: Bagan akun perkiraan (COA), Jurnal Umum, Neraca, dan Laporan Hasil Usaha (SHU).
  * ⚙️ **Konfigurasi Sistem & Audit**: Pengaturan suku bunga/plafon, manajemen user pengurus (RBAC), dan *Audit Log Viewer*.

### 2. Standar Tabel Data Finansial
* Mendukung pencarian cepat (*instant search* by NAK, NIP, Nama), filter rentang tanggal, filter status (Pending/Approved/Rejected).
* Tombol aksi ekspor wajib tersedia di setiap tabel finansial: **Ekspor PDF Resmi (Kop Surat Koperasi)** dan **Ekspor Excel (.xlsx)**.

### 3. Modal Aksi Sensitif & Konfirmasi
* Setiap aksi penting (misal: "Setujui Pencairan Pinjaman Rp 50.000.000", "Posting Jurnal Pembalik", "Aktivasi Agenda RAT") wajib menggunakan modal konfirmasi dengan rincian data finansial, input catatan persetujuan, dan verifikasi ulang password/PIN admin.
