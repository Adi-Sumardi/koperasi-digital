# Software Requirements Specification (SRS) - Koperasi Digital

## 1. Pendahuluan

### 1.1 Tujuan Dokumen
Dokumen Spesifikasi Kebutuhan Perangkat Lunak (SRS) ini dibuat untuk mendefinisikan kebutuhan teknis, fungsional, dan non-fungsional dari sistem **Koperasi Digital**. Dokumen ini ditujukan bagi tim pengembang (developer), desainer UI/UX, penguji (QA), serta pengurus koperasi sebagai acuan teknis selama proses implementasi sistem.

### 1.2 Deskripsi Umum Sistem
Sistem Koperasi Digital adalah platform berbasis web responsive yang memungkinkan anggota mengelola simpanan, mengajukan dan membayar pinjaman, memantau laporan akuntansi, serta berpartisipasi dalam e-voting RAT secara aman. Sistem ini menggunakan arsitektur modern berbasis API (API-First Design) untuk memastikan kemudahan integrasi dengan pihak ketiga (seperti Payment Gateway dan WhatsApp API).

---

## 2. Arsitektur Sistem & Spesifikasi Teknologi

Sistem dirancang dengan arsitektur 3-tier (Frontend, Backend API, Database) dengan rincian teknologi sebagai berikut:

*   **Frontend Web App:** Next.js (React) dengan Tailwind CSS, dikonfigurasi sebagai Progressive Web App (PWA) untuk performa optimal di perangkat mobile.
*   **Backend API Service:** Go (Golang) atau Node.js (NestJS) untuk memproses logika bisnis dengan performa tinggi.
*   **Database:** PostgreSQL (Database relasional untuk menjamin konsistensi data transaksi keuangan melalui fitur ACID transaction).
*   **Authentication & Security:** JSON Web Token (JWT) untuk manajemen sesi, bcrypt untuk hashing kata sandi, dan TLS/HTTPS untuk enkripsi jalur komunikasi data.
*   **Cache & Message Queue:** Redis untuk menyimpan session cache dan antrean notifikasi (SMS/WhatsApp).

---

## 3. Aktor & Matriks Hak Akses (Role-Based Access Control)

Sistem memiliki 4 aktor utama dengan pembagian hak akses sebagai berikut:

| Modul / Fitur | Anggota | Staf Admin | Pengurus | Pengawas |
| :--- | :---: | :---: | :---: | :---: |
| **Registrasi & KYC** | Input Data | Verifikasi | Lihat Laporan | - |
| **Simpanan Pokok & Wajib** | Lihat Riwayat | Input Setoran | Lihat Laporan | Audit Laporan |
| **Simpanan Sukarela** | Pengajuan Tarik | Proses Tarik | Lihat Laporan | Audit Laporan |
| **Pengajuan Pinjaman** | Input Pengajuan | Kelola Berkas | Setuju/Tolak | Lihat Berkas |
| **Pencairan Pinjaman** | Terima Dana | - | Proses Cair | Audit Laporan |
| **Pembayaran Cicilan** | Bayar Mandiri | Validasi Manual | Lihat Laporan | Audit Laporan |
| **Jurnal & Laporan Akuntansi**| - | Jurnal Manual | Lihat Laporan | Audit & Ekspor |
| **RAT Digital & E-Voting** | Isi Voting | Kelola Agenda | Kelola Agenda | Lihat Hasil |

---

## 4. Kebutuhan Fungsional (Functional Requirements)

### 4.1 Use Case Diagram
Berikut adalah visualisasi interaksi aktor dengan sistem Koperasi Digital:

```mermaid
usecaseDiagram
    actor "Anggota" as Member
    actor "Staf Admin" as Admin
    actor "Pengurus" as Board
    actor "Pengawas" as Supervisor

    Member --> (Registrasi & KYC KTP)
    Member --> (Lihat Saldo & Transaksi)
    Member --> (Pengajuan Pinjaman)
    Member --> (Bayar Angsuran)
    Member --> (Ikut E-Voting RAT)

    Admin --> (Verifikasi Calon Anggota)
    Admin --> (Input Kas Masuk/Keluar)
    Admin --> (Validasi Dokumen Pengajuan)

    Board --> (Persetujuan Pinjaman)
    Board --> (Pencairan Pinjaman)
    Board --> (Kelola Agenda RAT & E-Voting)

    Supervisor --> (Lihat Laporan Keuangan & Audit)
```

### 4.2 Deskripsi Detail Kebutuhan Fungsional

#### F-01: Manajemen Registrasi & KYC (Know Your Customer)
*   **Deskripsi:** Sistem harus memfasilitasi calon anggota untuk mendaftar secara mandiri melalui web.
*   **Alur Data:**
    1. Calon anggota mengisi form data pribadi (Nama, NIK, No. HP, Email, Alamat).
    2. Calon anggota mengunggah foto KTP dan Swafoto memegang KTP.
    3. Staf Admin menerima notifikasi pendaftaran di dashboard admin.
    4. Staf Admin mencocokkan data fisik dengan foto KTP.
    5. Staf Admin menekan tombol "Setujui" atau "Tolak" (disertai alasan penolakan).
    6. Jika disetujui, sistem menerbitkan Nomor Anggota (format: `KOP-YYYYMM-XXXX`) dan mengirimkan email aktivasi berisi tautan pembuatan kata sandi.

#### F-02: Transaksi Simpanan
*   **Deskripsi:** Sistem harus mencatat dan mengelola saldo Simpanan Pokok, Simpanan Wajib, dan Simpanan Sukarela milik Anggota.
*   **Spesifikasi:**
    *   Setiap kali status anggota aktif, sistem otomatis menerbitkan tagihan Simpanan Pokok (sekali saja) dan Simpanan Wajib bulanan (jatuh tempo setiap tanggal 10).
    *   Sistem terintegrasi dengan Payment Gateway (Virtual Account, E-Wallet, QRIS) untuk memfasilitasi pembayaran simpanan secara otomatis.
    *   Anggota dapat menarik Simpanan Sukarela mereka melalui sistem dengan mengajukan permohonan penarikan. Pengajuan ini harus diverifikasi oleh Staf Admin dan disetujui Bendahara sebelum dana ditransfer ke rekening bank terdaftar milik anggota.

#### F-03: Manajemen Siklus Pinjaman
*   **Deskripsi:** Sistem mengelola seluruh siklus pinjaman dari pengajuan hingga pelunasan.
*   **Spesifikasi:**
    *   **Simulasi:** Anggota dapat melakukan simulasi nominal pinjaman, tenor (bulan), dan jenis bunga untuk melihat perkiraan angsuran bulanan.
    *   **Pengajuan:** Anggota mengunggah dokumen slip gaji/bukti pendapatan dan mengisi nominal yang diajukan.
    *   **Analisis Risiko:** Sistem otomatis melakukan validasi aturan bisnis awal (misalnya: masa keanggotaan >= 3 bulan, tidak ada pinjaman aktif lain, dan nominal pengajuan <= 3x total simpanan).
    *   **Persetujuan:** Pengurus/Komite Kredit meninjau berkas pengajuan melalui dashboard dan memberikan status "Disetujui", "Ditolak", atau "Butuh Revisi Berkas".
    *   **Pencairan:** Bendahara mengonfirmasi pencairan dana melalui transfer bank (manual atau otomatis via API bank) dan menandai status pinjaman menjadi "Berjalan".
    *   **Pelunasan & Angsuran:** Sistem membuat jadwal angsuran bulanan. Anggota membayar angsuran via Payment Gateway. Keterlambatan pembayaran secara otomatis memicu perhitungan denda harian sebesar 0.1% dari nominal cicilan bulanan.

#### F-04: Sistem Akuntansi Otomatis (Double-Entry Bookkeeping)
*   **Deskripsi:** Setiap transaksi keuangan dalam sistem wajib memicu pencatatan jurnal akuntansi secara berpasangan (debet-kredit) secara real-time.
*   **Aturan Jurnal Otomatis:**
    *   *Pembayaran Simpanan Pokok:*
        *   **Debet:** Kas/Bank Koperasi
        *   **Kredit:** Ekuitas - Simpanan Pokok Anggota X
    *   *Pencairan Pinjaman:*
        *   **Debet:** Piutang Pinjaman Anggota X
        *   **Kredit:** Kas/Bank Koperasi
    *   *Pembayaran Angsuran Pinjaman:*
        *   **Debet:** Kas/Bank Koperasi
        *   **Kredit:** Piutang Pinjaman Anggota X (sebesar pokok angsuran)
        *   **Kredit:** Pendapatan Bunga Pinjaman (sebesar bunga angsuran)
*   **Laporan Output:** Sistem menyusun Neraca, Laporan Rugi Laba, Buku Besar per akun, dan Laporan SHU secara real-time yang dapat diakses oleh Pengurus dan Pengawas, serta diekspor ke format PDF/Excel.

#### F-05: Rapat Anggota Tahunan (RAT) & E-Voting
*   **Deskripsi:** Menyediakan wadah demokratis digital untuk pelaksanaan RAT tahunan.
*   **Spesifikasi:**
    *   Pengurus memposting modul RAT baru yang berisi dokumen laporan pertanggungjawaban (PDF) dan agenda voting.
    *   Anggota melakukan konfirmasi kehadiran digital untuk memenuhi persyaratan kuorum (misal: kuorum tercapai jika > 50% total anggota hadir secara digital).
    *   Sistem mengunci e-voting jika waktu voting telah berakhir. Anggota hanya memiliki hak 1 suara per akun (*one member, one vote*).
    *   Hasil voting ditampilkan secara transparan berupa grafik persentase suara masuk setelah sesi e-voting ditutup oleh Pengurus.

---

## 5. Kebutuhan Non-Fungsional (Non-Functional Requirements)

### 5.1 Keamanan (Security)
*   Komunikasi data wajib menggunakan protokol enkripsi HTTPS (SSL/TLS).
*   Manajemen otentikasi menggunakan JWT dengan masa kedaluwarsa token maksimal 1 hari.
*   Setiap request ke endpoint sensitif (pengubahan saldo keuangan, persetujuan pinjaman) wajib menyertakan verifikasi PIN transaksi 6-digit dari pengguna yang bersangkutan.
*   Log aktivitas (*Audit Trail*) mencatat IP Address, User Agent, Aksi, Waktu, dan Data lama/baru untuk setiap modifikasi data keuangan.

### 5.2 Keandalan & Performa (Reliability & Performance)
*   Sistem harus memiliki ketersediaan minimum (uptime) sebesar 99.9% (maksimal downtime 8.7 jam dalam setahun).
*   Waktu respons halaman web (Load Time) tidak boleh lebih dari 2 detik untuk jaringan 4G standar.
*   Sistem harus dapat menangani beban transaksi konkuren (Concurrent Transaction) hingga 500 transaksi per detik tanpa terjadi kegagalan basis data (*deadlock*).

### 5.3 Kemudahan Penggunaan (Usability)
*   Antarmuka pengguna harus memiliki desain yang responsif (Mobile-first design) agar dapat diakses dengan nyaman menggunakan smartphone dengan resolusi minimal 360x640 piksel.
*   Penerapan kontras warna teks dan latar belakang yang baik sesuai dengan standar WCAG 2.1 (minimum rasio kontras 4.5:1 untuk teks normal).

### 5.4 Spesifikasi PWA (Progressive Web App)
Untuk mendukung kenyamanan pengguna setara aplikasi native (Android/iOS), sistem frontend wajib memenuhi kriteria PWA berikut:
1.  **Web App Manifest (`manifest.json`):**
    *   `display`: `standalone` (menghilangkan address bar browser saat dibuka di smartphone).
    *   `orientation`: `portrait`.
    *   `icons`: Menyediakan icon aplikasi dalam berbagai ukuran standar (minimal 192x192px dan 512x512px).
    *   `theme_color` & `background_color`: Disesuaikan dengan warna primer desain koperasi digital (misal: Deep Emerald Green `#0d5c3a`).
2.  **Service Worker Caching & Offline Capabilities:**
    *   Implementasi caching untuk aset statis (HTML, JS, CSS, Font) menggunakan strategi *Stale-While-Revalidate* untuk mempercepat load time saat dibuka kembali.
    *   Menampilkan halaman offline kustom ("Tidak ada koneksi internet") jika pengguna mengakses fitur yang membutuhkan data dinamis tanpa koneksi.
    *   Mendukung penyimpanan offline read-only untuk data saldo terakhir menggunakan *IndexedDB* atau *localStorage*.
3.  **Installability (Web Install Prompt):**
    *   Menyediakan custom installer banner / prompt di dalam aplikasi untuk memandu anggota menginstal aplikasi koperasi ke layar beranda ponsel mereka.
4.  **Notifikasi Push (Push Notifications via Web Push API):**
    *   Terintegrasi dengan Web Push Service untuk mengirimkan push notification tagihan bulanan atau status pinjaman langsung ke perangkat ponsel anggota, bahkan saat browser sedang ditutup.

