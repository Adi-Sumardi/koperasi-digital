# Aturan Pengembangan: Glosarium & Istilah Terjemahan (*Translations*)

Dokumen ini mendefinisikan kamus istilah baku perkoperasian Indonesia yang wajib digunakan secara konsisten pada antarmuka mobile Flutter, API response, pesan kesalahan, dan dokumentasi teknis.

---

## 1. Nada Bahasa (*Tone of Voice*)
* **Kemitraan & Kepemilikan**: Anggota koperasi diperlakukan sebagai **pemilik sekaligus pengguna jasa** (*owner & user*), bukan sekadar "nasabah" atau "konsumen".
* **Sopan, Jelas & Transparan**: Hindari istilah perbankan yang kaku atau jargon asing yang membingungkan. Gunakan bahasa Indonesia yang baku sesuai pedoman Kementerian Koperasi & UKM RI.

---

## 2. Glosarium Istilah Perkoperasian (ID ⇄ EN)

| Istilah Bahasa Indonesia (UI/User) | Kode / Atribut Teknis (EN) | Definisi & Ketentuan Bisnis |
| :--- | :--- | :--- |
| **Anggota** | `Member` / `member_id` | Individu yang telah memenuhi syarat pendaftaran dan memiliki NAK sah. |
| **Nomor Anggota Koperasi (NAK)** | `member_number` | Identitas unik anggota format: `KOP-YYYY-XXXXX`. |
| **Simpanan Pokok** | `Principal Savings` / `simpanan_pokok` | Jumlah uang yang wajib dibayarkan sekali saat menjadi anggota; tidak dapat ditarik selama masih menjadi anggota. |
| **Simpanan Wajib** | `Mandatory Savings` / `simpanan_wajib` | Simpanan tertentu yang harus dibayarkan setiap bulan; tidak dapat ditarik selama masih menjadi anggota. |
| **Simpanan Sukarela** | `Voluntary Savings` / `simpanan_sukarela` | Simpanan bebas yang dapat disetor dan ditarik sewaktu-waktu; berfungsi sebagai saldo dompet digital anggota. |
| **Pinjaman / Pembiayaan** | `Loan` / `loan_application` | Fasilitas kredit yang diberikan koperasi kepada anggota dengan kesepakatan pengembalian. |
| **Plafon Pinjaman** | `Loan Ceiling` / `loan_limit` | Batas maksimum pinjaman yang disetujui (umumnya proporsional terhadap total simpanan & gaji). |
| **Tenor** | `Tenor` / `tenor_months` | Jangka waktu pinjaman (dalam hitungan bulan). |
| **Bunga Flat** | `Flat Interest` / `flat_rate` | Perhitungan bunga tetap setiap bulan dari pokok pinjaman awal. |
| **Bunga Menurun / Sliding** | `Sliding Interest` / `effective_rate` | Perhitungan bunga berdasarkan sisa pokok pinjaman yang terus berkurang setiap bulan. |
| **Angsuran / Cicilan** | `Installment` / `loan_installment` | Pembayaran bulanan yang terdiri dari porsi Pokok Pinjaman + Jasa/Bunga. |
| **Pelunasan Dipercepat** | `Early Settlement` | Pembayaran seluruh sisa pokok pinjaman sebelum jatuh tempo tenor. |
| **Sisa Hasil Usaha (SHU)** | `Surplus / Profit Share` / `shu` | Pendapatan koperasi yang diperoleh dalam satu tahun buku dikurangi beban operasional; dibagikan kepada anggota. |
| **Jasa Modal** | `Capital Share` / `shu_jasa_modal` | Bagian SHU yang dibagikan berdasarkan besarnya simpanan (pokok + wajib) anggota. |
| **Jasa Usaha / Anggota** | `Member Business Share` / `shu_jasa_usaha` | Bagian SHU yang dibagikan berdasarkan besarnya transaksi belanja atau bunga pinjaman anggota. |
| **Rapat Anggota Tahunan (RAT)** | `Annual Meeting` / `rat_session` | Pemegang kekuasaan tertinggi dalam tata kelola koperasi. |
| **Kuorum** | `Quorum` / `is_quorum_reached` | Jumlah minimal kehadiran anggota yang sah untuk mengambil keputusan pada RAT. |
| **E-Voting** | `Electronic Voting` / `rat_vote` | Pemungutan suara digital atas agenda kerja, LPJ, atau pemilihan pengurus. |
| **Laporan Pertanggungjawaban (LPJ)**| `Accountability Report` / `lpj_document` | Dokumen evaluasi kinerja tahunan pengurus dan pengawas. |
| **Buku Besar Berpasangan** | `Double-Entry General Ledger` | Sistem akuntansi di mana setiap debit diimbangi dengan kredit yang seimbang. |
| **Bagan Akun (COA)** | `Chart of Accounts` / `coa` | Daftar kode perkiraan akuntansi standar koperasi. |
| **Kopmart** | `Cooperative Store` / `kopmart` | Unit usaha ritel/toko koperasi untuk memenuhi kebutuhan belanja anggota. |
| **Potong Gaji (Payroll Deduction)** | `Payroll Deduction` / `payroll_cut` | Mekanisme pembayaran iuran atau angsuran yang dipotong langsung dari slip gaji bulanan. |

---

## 3. Pesan Sistem & Validasi (*Standard Error & Success Messages*)

### Pesan Berhasil (*Success Copy*)
* **Registrasi Berhasil**: *"Pendaftaran Anda berhasil. Silakan selesaikan pembayaran Simpanan Pokok untuk mengaktifkan keanggotaan."*
* **Setoran Sukses**: *"Setoran Simpanan [Jenis] sebesar [Nominal] berhasil dibukukan."*
* **Pengajuan Pinjaman Terkirim**: *"Pengajuan pinjaman Anda telah kami terima dan sedang diproses oleh Komite Kredit Koperasi."*
* **Voting RAT Tercatat**: *"Hak suara Anda untuk agenda ini telah berhasil diverifikasi dan tersimpan secara anonim."*

### Pesan Gagal (*Error Copy*)
* **Saldo Kurang**: *"Saldo Simpanan Sukarela Anda tidak mencukupi untuk melakukan transaksi ini."*
* **Plafon Melebihi Batas**: *"Nominal pinjaman melebihi batas plafon yang diizinkan (Maksimal [Plafon])."*
* **Kuorum Belum Tercapai**: *"Voting tidak dapat ditutup karena kuorum minimal [x]% anggota belum terpenuhi."*
* **Rekening Belum Aktif**: *"Keanggotaan Anda belum aktif. Mohon hubungi pengurus atau selesaikan verifikasi KYC."*
