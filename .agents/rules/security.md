# Aturan Pengembangan: Standar Keamanan Sistem Finansial (*Security*)

Dokumen ini mendefinisikan arsitektur keamanan, proteksi data pribadi anggota, pencegahan kecurangan (*fraud prevention*), dan kepatuhan standar industri fintech untuk Koperasi Digital.

---

## 1. Otentikasi & Otorisasi (*Auth & Access Control*)

1. **Kanal Web Superadmin (Session-Based Auth & 2FA)**:
   * Portal Web Laravel khusus Super Admin & Pengurus menggunakan autentikasi berbasis sesi aman (*stateful session cookies*).
   * Cookie wajib memiliki flag: `HttpOnly`, `Secure`, dan `SameSite=Strict` untuk mencegah pencurian token via serangan XSS.
   * Dilengkapi proteksi **CSRF Token** mutlak di setiap form POST/PUT/DELETE.
   * **Inactivity Session Timeout**: Sesi admin otomatis kedaluwarsa setelah 30 menit tidak ada aktivitas.
   * Mendukung Two-Factor Authentication (**2FA TOTP**) untuk aksi Super Admin dan persetujuan pencairan pinjaman bernilai besar.
2. **Kanal Mobile Flutter Anggota (Sanctum Bearer Token & PIN)**:
   * Aplikasi mobile anggota menggunakan **Laravel Sanctum Personal Access Token** yang disimpan di penyimpanan aman tingkat perangkat keras (**`flutter_secure_storage`** - Keystore Android / Keychain iOS).
   * Dilarang menyimpan token di `SharedPreferences` atau `localStorage`.
   * **PIN Transaksi 6-Digit & Biometrik**:
     * Setiap aksi pemindahan dana, penarikan simpanan, pengajuan pinjaman, atau pembayaran QRIS **wajib** memvalidasi PIN Transaksi atau biometrik (*Fingerprint / Face ID*).
     * PIN di-hash menggunakan algoritma **Argon2id** atau **Bcrypt** dengan *cost factor* memadai.
     * **Proteksi Brute-Force PIN**: Dibatasi maksimal 3 kali percobaan salah. Jika gagal 3 kali berturut-turut, akun terkunci selama 15 menit dan notifikasi keamanan dikirimkan ke WhatsApp/Email anggota.

---

## 2. Enkripsi Data Sensitif (*Data Protection & Privacy*)

1. **Perlindungan PII (Personally Identifiable Information)**:
   * Kolom data sensitif kependudukan seperti **NIK**, **Nomor Rekening Bank**, dan **Gaji Pokok** wajib dienkripsi saat disimpan di PostgreSQL menggunakan fitur enkripsi Laravel (`'nik' => 'encrypted'`).
2. **Keamanan Berkas KYC (Foto KTP & Selfie)**:
   * Foto KTP tidak boleh diletakkan di folder publik web (`public/`).
   * Berkas disimpan di *private bucket* (MinIO / S3 Private) dan hanya dapat diakses melalui URL bertanda tangan berwaktu terbatas (*Presigned URL*, berlaku maksimal 5 menit).
   * Validasi ketat saat upload: tipe MIME hanya `image/jpeg` atau `image/png`, ukuran maksimal 5 MB.

---

## 3. Pencegahan Celah Finansial (*Anti-Fraud & Integrity*)

1. **Jejak Audit Mutlak (*Immutable Audit Trail*)**:
   * Setiap aksi finansial dan administratif dicatat dalam tabel `audit_logs`:
     * `user_id`: Aktor yang melakukan aksi.
     * `event`: Jenis kejadian (misal: `loan.disbursed`, `member.kyc_approved`).
     * `ip_address`: Alamat IP klien.
     * `user_agent`: Info perangkat.
     * `old_values`: Nilai sebelum perubahan (JSON).
     * `new_values`: Nilai sesudah perubahan (JSON).
2. **Pencegahan Double-Spending / Balapan Transaksi**:
   * Dilarang membaca saldo lalu mengurangkannya di aplikasi tanpa *database lock*.
   * Wajib menggunakan `lockForUpdate()` di dalam transaksi database PostgreSQL untuk memastikan isolasi baris.
3. **Idempotency Protection**:
   * Semua endpoint finansial wajib menyertakan header `X-Idempotency-Key` bertipe UUID v4 untuk menolak pengiriman berulang.

---

## 4. Keamanan Jaringan & Komunikasi (*Network Security*)

* **Enkripsi Transit**: Wajib menggunakan protokol **TLS 1.3** untuk seluruh lalu lintas API.
* **Certificate Pinning**: Pada build produksi aplikasi Flutter, terapkan SSL Pinning menggunakan fingerprint SHA-256 sertifikat server untuk mencegah serangan *Man-in-the-Middle (MitM)*.
* **Header Keamanan HTTP**: Backend wajib menyertakan header proteksi:
  * `X-Content-Type-Options: nosniff`
  * `X-Frame-Options: DENY`
  * `X-XSS-Protection: 1; mode=block`
  * `Strict-Transport-Security: max-age=31536000; includeSubDomains`
