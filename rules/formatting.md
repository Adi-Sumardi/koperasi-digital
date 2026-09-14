# Aturan Pengembangan: Standar Format Data (*Formatting*)

Dokumen ini merumuskan standar pemformatan mata uang, tanggal, persentase, identitas kependudukan, dan nomor referensi untuk menjamin konsistensi di antarmuka Flutter dan respons API Laravel 13.

---

## 1. Format Mata Uang Rupiah (IDR)

* **Standar Simbol**: Selalu gunakan `Rp ` (dengan satu spasi setelahnya).
* **Pemisah Ribuan**: Titik (`.`).
* **Pemisah Desimal**: Koma (`,`).
* **Angka Nol Desimal**: Sembunyikan desimal jika bernilai nol bulat (e.g., `Rp 1.500.000`, bukan `Rp 1.500.000,00`), kecuali pada laporan akuntansi buku besar yang memerlukan presisi 2 digit sen.

### Contoh Implementasi (Flutter)
```dart
import 'package:intl/intl.dart';

class CurrencyFormatter {
  static final _formatterNoDecimals = NumberFormat.currency(
    locale: 'id_ID',
    symbol: 'Rp ',
    decimalDigits: 0,
  );

  static final _formatterWithDecimals = NumberFormat.currency(
    locale: 'id_ID',
    symbol: 'Rp ',
    decimalDigits: 2,
  );

  /// Menghasilkan format standar UI: Rp 1.500.000
  static String format(num amount, {bool showDecimals = false}) {
    if (showDecimals) {
      return _formatterWithDecimals.format(amount);
    }
    return _formatterNoDecimals.format(amount);
  }

  /// Menghasilkan format ringkas untuk grafik/badge: Rp 1,5 Jt
  static String formatCompact(num amount) {
    if (amount >= 1000000000) {
      return 'Rp ${(amount / 1000000000).toStringAsFixed(1)} M';
    } else if (amount >= 1000000) {
      return 'Rp ${(amount / 1000000).toStringAsFixed(1)} Jt';
    } else if (amount >= 1000) {
      return 'Rp ${(amount / 1000).toStringAsFixed(1)} Rb';
    }
    return format(amount);
  }
}
```

### Contoh Implementasi (Laravel)
```php
namespace App\Support;

class Formatter
{
    public static function rupiah(float|int|string $amount, int $decimals = 0): string
    {
        return 'Rp ' . number_format((float) $amount, $decimals, ',', '.');
    }
}
```

---

## 2. Format Tanggal & Waktu (*DateTime*)

Selalu gunakan zona waktu Indonesia Barat (**WIB / Asia/Jakarta**) sebagai default sistem.

| Tipe Data | Format Standar | Contoh Output |
| :--- | :--- | :--- |
| **Tanggal Lengkap** | `dd MMMM yyyy` | `14 September 2026` |
| **Tanggal Singkat** | `dd/MM/yyyy` | `14/09/2026` |
| **Tanggal & Jam Mutasi** | `dd MMM yyyy, HH:mm 'WIB'` | `14 Sep 2026, 14:30 WIB` |
| **Bulan & Tahun Buku** | `MMMM yyyy` | `September 2026` |
| **Log Audit / Sistem ISO** | ISO-8601 (UTC) | `2026-09-14T07:30:00.000000Z` |

---

## 3. Format Identitas & Dokumen

1. **Nomor Anggota Koperasi (NAK)**:
   * Pola: `KOP-YYYY-XXXXX`
   * Contoh: `KOP-2026-00142` (Koperasi, Tahun bergabung, 5 digit urutan).
2. **NIK (Nomor Induk Kependudukan)**:
   * Format input: 16 digit angka tanpa spasi.
   * Format tampilan tersamar (*masked privacy*): `3201 0420 •••• 0005`.
3. **Nomor Handphone / WhatsApp**:
   * Format penyimpanan: E.164 (`+6281234567890`).
   * Format tampilan UI: `0812-3456-7890`.
4. **Nomor Referensi Transaksi (Jurnal / Pembayaran)**:
   * Pola: `TRX-[KODE]-[YYYYMMDD]-[RANDOM6]`
   * Contoh: `TRX-SAV-20260914-948210` (Setoran Simpanan), `TRX-LON-20260914-110294` (Pencairan Pinjaman).

---

## 4. Format Persentase & Tenor Finansial

* **Suku Bunga Bulanan**: Selalu sebutkan tenor waktu: `0,8% / bulan` (gunakan koma untuk desimal).
* **Suku Bunga Tahunan**: `9,6% p.a.` (*per annum*).
* **Tenor Pinjaman**: `12 Bulan` (hindari singkatan "12 bln" pada lembar akad resmi).
* **Proporsi SHU**: `40% Jasa Modal`, `30% Jasa Usaha`.
