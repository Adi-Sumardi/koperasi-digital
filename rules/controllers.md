# Aturan Pengembangan: Standar Controller Laravel 13

Dokumen ini menetapkan standar penulisan controller di Laravel 13 untuk API backend Koperasi Digital. Tujuannya adalah memastikan controller tetap ramping (*thin controllers*), mudah diuji, dan aman dari celah keamanan.

---

## 1. Prinsip Utama: *Thin Controllers & Action Delegation*

1. **Dilarang Menaruh Logika Bisnis di Controller**:
   * Controller hanya bertugas:
     1. Menerima permintaan HTTP.
     2. Melakukan otorisasi & validasi via **Form Request**.
     3. Meneruskan data ke **Action / Service**.
     4. Mengembalikan respons:
        * **Web Admin (Superadmin)**: Mengembalikan tampilan Blade / Inertia View atau Redirect (`View / RedirectResponse`).
        * **Mobile API (Anggota Flutter)**: Mengembalikan JSON terformat via **API Resource** (`JsonResponse`).
2. **Pemisahan Namespace Controller Berdasarkan Kanal**:
   * **`App\Http\Controllers\Web\Admin\...`**:
     * Khusus untuk antarmuka **Web Superadmin & Pengurus**.
     * Menggunakan middleware grup `web` (Session Cookies, CSRF Protection, Superadmin Role Guard).
   * **`App\Http\Controllers\Api\V1\...`**:
     * Khusus untuk aplikasi **Mobile Flutter Anggota**.
     * Menggunakan middleware grup `api` (Stateless, Bearer Token Sanctum, Idempotency Key, Rate Limiter).
3. **Utamakan Single Action (Invokable) Controllers untuk Transaksi**:
   * Untuk operasi kompleks finansial (misal: pengajuan pinjaman, approval kredit, pencairan dana, pembayaran QRIS), gunakan invokable controller (`__invoke`).
   * Gunakan Resource Controller standar hanya untuk operasi CRUD master data di Web Admin.
4. **Kepatuhan Tipe Ketat (*Strict Types*)**:
   * Seluruh berkas controller wajib diawali `declare(strict_types=1);`.
   * Setiap parameter dan *return type* wajib dideklarasikan secara eksplisit.

---

## 2. Struktur Form Request & Validasi

Setiap endpoint yang menerima *input* (POST, PUT, PATCH) **wajib** menggunakan Form Request terpisah. Dilarang memanggil `$request->validate()` di dalam controller.

```php
declare(strict_types=1);

namespace App\Http\Requests\Loans;

use Illuminate\Foundation\Http\FormRequest;

class ApplyLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Pastikan anggota berstatus aktif
        return $this->user()?->member?->isActive() ?? false;
    }

    public function rules(): array
    {
        return [
            'loan_product_id' => ['required', 'uuid', 'exists:loan_products,id'],
            'amount'          => ['required', 'numeric', 'min:500000', 'max:100000000'],
            'tenor_months'    => ['required', 'integer', 'in:3,6,12,24,36'],
            'purpose'         => ['required', 'string', 'max:500'],
            'guarantee_type'  => ['nullable', 'string', 'in:payroll,bpjs,vehicle,property'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.min' => 'Pengajuan pinjaman minimal Rp 500.000.',
            'amount.max' => 'Nominal pinjaman melebihi batas sistem.',
        ];
    }
}
```

---

## 3. Struktur Invokable Controller (Contoh Finansial)

```php
declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Loans;

use App\Actions\Loans\ApplyLoanAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Loans\ApplyLoanRequest;
use App\Http\Resources\LoanApplicationResource;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApplyLoanController extends Controller
{
    public function __invoke(
        ApplyLoanRequest $request,
        ApplyLoanAction $action
    ): JsonResponse {
        $loanApplication = $action->execute(
            member: $request->user()->member,
            data: $request->validated()
        );

        return ApiResponse::success(
            data: new LoanApplicationResource($loanApplication),
            message: 'Pengajuan pinjaman berhasil dikirimkan.',
            code: Response::HTTP_CREATED
        );
    }
}
```

---

## 4. Standar Pembungkus Respons (*Response Envelope*)

Semua controller wajib mengembalikan format respons standar JSON:

### Format Sukses
```json
{
  "success": true,
  "message": "Pengajuan pinjaman berhasil dikirimkan.",
  "data": {
    "id": "9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d",
    "reference_no": "TRX-LON-20260914-0012",
    "amount": "15000000.00",
    "status": "pending_review"
  },
  "meta": {
    "timestamp": "2026-09-14T13:30:00Z",
    "api_version": "v1"
  }
}
```

### Format Gagal / Validasi
```json
{
  "success": false,
  "message": "Data pengajuan tidak valid.",
  "errors": {
    "amount": ["Pengajuan pinjaman minimal Rp 500.000."]
  },
  "code": "VALIDATION_ERROR"
}
```

---

## 5. Pemetaan Kode Status HTTP (*HTTP Status Codes*)

| Status Code | Penggunaan |
| :--- | :--- |
| **200 OK** | Permintaan baca (GET), update sukses (PUT/PATCH), atau aksi sukses. |
| **201 Created** | Berhasil membuat entitas baru (POST registrasi, buat pinjaman, setor simpanan). |
| **204 No Content** | Operasi sukses tanpa konten kembali (DELETE). |
| **400 Bad Request** | Kesalahan logika bisnis (e.g., saldo tidak cukup, batas kuorum lewat). |
| **401 Unauthorized** | Token Sanctum hilang, kedaluwarsa, atau tidak valid. |
| **403 Forbidden** | Pengguna terautentikasi tetapi tidak memiliki hak akses (*role/permission*). |
| **404 Not Found** | Data/entitas tidak ditemukan. |
| **422 Unprocessable Entity** | Gagal validasi form request. |
| **429 Too Many Requests** | Terkena *rate limiting* (misal: spam coba PIN transaksi). |
| **500 Server Error** | Kesalahan sistem internal tak terduga (wajib dicatat di logger Sentry/Bugsnag). |
