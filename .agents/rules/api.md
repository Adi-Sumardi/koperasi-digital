# Aturan Pengembangan: Standar RESTful API

Dokumen ini merumuskan spesifikasi kontrak antarmuka API antara backend Laravel 13 dan aplikasi mobile Flutter Koperasi Digital.

---

## 1. Konvensi URI & Versioning

* **Base URL**: `/api/v1`
* **Format Penamaan**: Gunakan kata benda jamak (*plural nouns*) dengan format *kebab-case*:
  * `/api/v1/savings-accounts`
  * `/api/v1/loan-applications`
  * `/api/v1/rat-sessions`
  * `/api/v1/qris-transactions`
* **Sub-resource untuk Relasi**:
  * `/api/v1/loans/{id}/installments` (Melihat jadwal angsuran pinjaman tertentu)
  * `/api/v1/rat-sessions/{id}/votes` (Memberikan suara pada agenda RAT tertentu)

---

## 2. Header Permintaan (*Request Headers*)

Setiap panggilan API dari Flutter wajib menyertakan:
```http
Accept: application/json
Content-Type: application/json
Authorization: Bearer <token_sanctum>
X-App-Version: 1.0.0
X-Idempotency-Key: <uuid-v4>  // WAJIB untuk semua POST transaksi finansial
```

---

## 3. Skema Amplop Respons (*Response Envelope*)

### 1. Respons Berhasil (*Success 200/201*)
```json
{
  "success": true,
  "message": "Pengajuan pinjaman berhasil dibuat.",
  "data": {
    "id": "3fa85f64-5717-4562-b3fc-2c963f66afa6",
    "reference_no": "TRX-LON-20260914-0021",
    "amount": "10000000.00",
    "tenor_months": 12,
    "monthly_installment": "913333.33",
    "status": "pending_review"
  },
  "meta": {
    "timestamp": "2026-09-14T13:45:00Z",
    "version": "1.0.0"
  }
}
```

### 2. Respons Paginasi (*Paginated List*)
```json
{
  "success": true,
  "message": "Riwayat transaksi berhasil diambil.",
  "data": [
    {
      "id": "...",
      "title": "Setoran Simpanan Wajib",
      "amount": "150000.00",
      "type": "deposit",
      "created_at": "2026-09-10T08:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 45,
    "last_page": 3
  }
}
```

### 3. Respons Gagal / Validasi (*Error 422 / 400*)
```json
{
  "success": false,
  "message": "Terjadi kesalahan validasi data.",
  "errors": {
    "amount": ["Nominal pinjaman melebihi batas plafon simpanan Anda."],
    "tenor_months": ["Tenor yang dipilih tidak tersedia."]
  },
  "code": "VALIDATION_FAILED"
}
```

---

## 4. Idempotency Key untuk Transaksi Finansial

Untuk mencegah pendebetan/pencairan ganda akibat koneksi seluler terputus atau pengguna menekan tombol berulang kali:
1. Flutter membuat `X-Idempotency-Key` bertipe UUID v4 acak sebelum mengirimkan request POST finansial.
2. Middleware Laravel mengecek `X-Idempotency-Key` di cache Redis:
   * Jika key sedang diproses, tolak dengan status `409 Conflict`.
   * Jika key sudah selesai dalam 24 jam terakhir, kembalikan respons tersimpan sebelumnya (*cached response*) tanpa mengeksekusi ulang transaksi.

---

## 5. Peta Endpoint Inti (*Core Endpoints Map*)

| Modul | Method | Endpoint | Deskripsi |
| :--- | :--- | :--- | :--- |
| **Auth** | `POST` | `/api/v1/auth/register` | Pendaftaran akun anggota baru |
| | `POST` | `/api/v1/auth/login` | Login anggota & penerbitan token Sanctum |
| | `GET` | `/api/v1/auth/me` | Profil data anggota aktif |
| | `POST` | `/api/v1/auth/kyc/submit` | Unggah data & foto KTP |
| **Simpanan** | `GET` | `/api/v1/savings` | Ringkasan saldo Pokok, Wajib, Sukarela |
| | `POST` | `/api/v1/savings/deposit` | Inisiasi setoran via VA / QRIS |
| | `POST` | `/api/v1/savings/withdraw` | Penarikan saldo sukarela ke rekening bank |
| **Pinjaman**| `GET` | `/api/v1/loans/simulate` | Hitung simulasi angsuran (Flat/Sliding) |
| | `POST` | `/api/v1/loans/apply` | Pengajuan pinjaman baru |
| | `GET` | `/api/v1/loans` | Daftar pinjaman aktif & riwayat |
| | `POST` | `/api/v1/loans/{id}/repay` | Pembayaran cicilan bulanan |
| **Kopmart** | `GET` | `/api/v1/kopmart/products` | Katalog produk belanja |
| | `POST` | `/api/v1/kopmart/checkout` | Checkout belanja (potong saldo/kasbon) |
| **QRIS** | `POST` | `/api/v1/qris/scan` | Validasi payload QRIS dinamis/statis |
| | `POST` | `/api/v1/qris/pay` | Eksekusi pembayaran merchant QRIS |
| **RAT** | `GET` | `/api/v1/rat/sessions/active`| Cek sesi RAT & unduh berkas LPJ |
| | `POST` | `/api/v1/rat/agendas/{id}/vote`| Berikan suara e-voting |
| **SHU** | `GET` | `/api/v1/shu/estimate` | Estimasi dividen SHU tahun berjalan |
| **Mutasi** | `GET` | `/api/v1/transactions` | Riwayat mutasi debit/kredit |
