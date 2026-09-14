# Aturan Pengembangan: Standar Eloquent Model Laravel 13

Dokumen ini mendefinisikan aturan perancangan Eloquent Model pada backend Koperasi Digital. Model Eloquent bertanggung jawab atas integritas data, relasi, *type casting*, dan pengamanan akses.

---

## 1. Konvensi Kunci Utama (*Primary Key*)

* Gunakan **UUID v7** (`HasUuids` trait) untuk entitas yang bersifat transaksi publik, anggota, pinjaman, dan pembayaran:
  * Mencegah eksploitasi *sequential ID enumeration* (misal: menebak nomor ID pinjaman orang lain).
  * UUID v7 berbasis *timestamp* terurut sehingga mempertahankan performa *B-Tree index* di PostgreSQL.
* Tabel master referensi kecil (misal: jenis jaminan, kategori produk) dapat menggunakan `bigIncrements`.

---

## 2. Mass Assignment & Proteksi Keamanan

* **Wajib mendefinisikan `$fillable` secara eksplisit**:
  ```php
  protected $fillable = [
      'member_id',
      'loan_product_id',
      'principal_amount',
      'interest_rate',
      'tenor_months',
      'status',
  ];
  ```
* **Dilarang keras menggunakan `protected $guarded = [];`** pada seluruh model entitas finansial untuk mencegah celah injeksi mass assignment.

---

## 3. Atribut & Modern Type Casting (`casts()`)

Gunakan metode `casts(): array` (standar modern Laravel):

```php
declare(strict_types=1);

namespace App\Models;

use App\Enums\LoanStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loan extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'member_id',
        'loan_product_id',
        'reference_no',
        'principal_amount',
        'interest_rate',
        'tenor_months',
        'monthly_installment',
        'outstanding_balance',
        'status',
        'approved_at',
        'disbursed_at',
    ];

    /**
     * Modern type casting Laravel 13
     */
    protected function casts(): array
    {
        return [
            'principal_amount'    => 'decimal:2',
            'interest_rate'       => 'decimal:4',
            'monthly_installment' => 'decimal:2',
            'outstanding_balance' => 'decimal:2',
            'tenor_months'        => 'integer',
            'status'              => LoanStatus::class,
            'approved_at'         => 'immutable_datetime',
            'disbursed_at'        => 'immutable_datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function installments(): HasMany
    {
        return $this->hasMany(LoanInstallment::class);
    }
}
```

---

## 4. Integritas Relasi & Pencegahan N+1 Query

1. **Deklarasi Return Type Relasi**:
   * Setiap metode relasi wajib memiliki deklarasi tipe eksplisit: `BelongsTo`, `HasMany`, `HasOne`, dll.
2. **Pencegahan Lazy Loading**:
   * Di dalam `AppServiceProvider`, aktifkan aturan:
     ```php
     Model::preventLazyLoading(! app()->isProduction());
     Model::preventSilentlyDiscardingAttributes(! app()->isProduction());
     ```
   * Pengembang wajib menggunakan *eager loading* (`with(['member', 'installments'])`) pada kueri yang mengembalikan sekumpulan data.

---

## 5. Aturan Imutabilitas Data Finansial (*Immutability Rules*)

Entitas pembukuan (*JournalEntry*, *JournalLine*, *TransactionHistory*) **tidak boleh dapat diubah atau dihapus** setelah disimpan. Terapkan guard pada model boot:

```php
namespace App\Models;

use App\Exceptions\FinancialImmutableException;
use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    protected static function booted(): void
    {
        static::updating(function () {
            throw new FinancialImmutableException('Jurnal akuntansi yang telah diposting tidak boleh diedit.');
        });

        static::deleting(function () {
            throw new FinancialImmutableException('Jurnal akuntansi tidak boleh dihapus. Gunakan mekanisme jurnal pembalik.');
        });
    }
}
```

---

## 6. Penggunaan Backed Enums PHP

Semua status alur kerja finansial wajib menggunakan PHP 8.2+ Backed Enums:
* `LoanStatus`: `PENDING`, `APPROVED`, `REJECTED`, `DISBURSED`, `ACTIVE`, `PAID_OFF`, `DEFAULTED`.
* `SavingsType`: `POKOK`, `WAJIB`, `SUKARELA`.
* `TransactionType`: `DEPOSIT`, `WITHDRAWAL`, `LOAN_DISBURSEMENT`, `LOAN_REPAYMENT`, `KOPMART_PURCHASE`, `SHU_DISTRIBUTION`.
* `AccountCategory`: `ASSET`, `LIABILITY`, `EQUITY`, `REVENUE`, `EXPENSE`.
