# Aturan Pengembangan: Arsitektur Aplikasi Mobile Flutter

Dokumen ini merumuskan standar arsitektur perangkat lunak, struktur direktori, manajemen state, dan tata kelola dependensi untuk aplikasi mobile Flutter Koperasi Digital.

---

## 1. Pendekatan Arsitektur: *Feature-First Clean Architecture*

Aplikasi disusun menggunakan prinsip Clean Architecture dengan pengelompokan berbasis fitur (*feature-first*):

```
lib/
├── app/
│   ├── app.dart               # Root MaterialApp & konfigurasi
│   ├── router.dart            # Konfigurasi GoRouter & Auth Guards
│   └── theme.dart             # ThemeData, ColorScheme & Font
├── core/
│   ├── constants/             # API Endpoints, AppConstants
│   ├── errors/                # Failure & Exception classes
│   ├── network/               # Dio Client, Auth & Idempotency Interceptor
│   ├── storage/               # SecureStorage & SharedPreferences helper
│   └── utils/                 # CurrencyFormatter, DateFormatter
├── features/
│   ├── auth/                  # Registrasi, Login, KYC
│   ├── home/                  # Beranda Anggota & Saldo Ringkasan
│   ├── savings/               # Pokok, Wajib, Sukarela & Mutasi
│   ├── loans/                 # Simulasi, Pengajuan & Angsuran Pinjaman
│   ├── kopmart/               # Belanja Karyawan & Keranjang
│   ├── qris/                  # Scanner & Konfirmasi Pembayaran QRIS
│   ├── rat/                   # Agenda RAT, LPJ & E-Voting
│   └── profile/               # Akun Anggota, Ganti PIN & Keamanan
└── shared/
    ├── widgets/               # Reusable buttons, cards, badges, bottom bar
    └── extensions/            # Context extensions, string formatters
```

### Struktur Internal Setiap Modul Fitur (`features/<nama_fitur>/`):
1. **`data/`**:
   * `datasources/`: Remote data source (Dio) & Local data source (Cache).
   * `models/`: DTO (*Data Transfer Object*) dengan serialisasi JSON (`freezed` / `json_serializable`).
   * `repositories/`: Implementasi konkret dari domain repository.
2. **`domain/`**:
   * `entities/`: Model murni domain bisnis.
   * `repositories/`: Interface kontrak repository (*abstract class*).
   * `usecases/`: Logika bisnis spesifik (misal: `CalculateLoanSimulation`, `SubmitSavingsDeposit`).
3. **`presentation/`**:
   * **`bloc/`**: Manajemen state BLoC:
     * `*_event.dart`: Event masukan dari UI (subclass dari `Equatable`).
     * `*_state.dart`: Status UI (Initial, Loading, Loaded, Error) (subclass dari `Equatable`).
     * `*_bloc.dart`: Handler logika bisnis (`on<Event>((event, emit) async { ... })`).
   * `screens/`: Layar tampilan penuh (*Scaffold*) yang membungkus `BlocBuilder` / `BlocConsumer`.
   * `widgets/`: Sub-widget modular spesifik untuk fitur tersebut.

---

## 2. Manajemen State (*State Management with Flutter BLoC*)

Aplikasi **wajib menggunakan Flutter BLoC (`flutter_bloc`)** untuk memisahkan logika presentasi dari antarmuka pengguna:

### Konvensi Struktur BLoC:
1. **Event (`*_event.dart`)**:
   ```dart
   abstract class SavingsEvent extends Equatable {
     const SavingsEvent();
     @override
     List<Object?> get props => [];
   }

   class FetchSavingsOverviewEvent extends SavingsEvent {}

   class SubmitDepositEvent extends SavingsEvent {
     final double amount;
     final SavingsType type;
     const SubmitDepositEvent({required this.amount, required this.type});
     @override
     List<Object?> get props => [amount, type];
   }
   ```

2. **State (`*_state.dart`)**:
   ```dart
   abstract class SavingsState extends Equatable {
     const SavingsState();
     @override
     List<Object?> get props => [];
   }

   class SavingsInitialState extends SavingsState {}
   class SavingsLoadingState extends SavingsState {}
   class SavingsLoadedState extends SavingsState {
     final SavingsOverview overview;
     const SavingsLoadedState(this.overview);
     @override
     List<Object?> get props => [overview];
   }
   class SavingsErrorState extends SavingsState {
     final String message;
     const SavingsErrorState(this.message);
     @override
     List<Object?> get props => [message];
   }
   ```

3. **Konsumsi di Widget Tampilan UI**:
   Gunakan **`BlocConsumer`** jika membutuhkan pendengar aksi sekali jalan (*side-effects* seperti `SnackBar`, `Dialog`, atau navigasi saat error/success) sekaligus merender tampilan:
   ```dart
   BlocConsumer<SavingsBloc, SavingsState>(
     listener: (context, state) {
       if (state is SavingsErrorState) {
         ScaffoldMessenger.of(context).showSnackBar(
           SnackBar(content: Text(state.message), backgroundColor: AppColors.error),
         );
       }
     },
     builder: (context, state) {
       if (state is SavingsLoadingState) {
         return const SavingsShimmerLoading();
       } else if (state is SavingsLoadedState) {
         return SavingsContent(overview: state.overview);
       }
       return const SizedBox.shrink();
     },
   )
   ```

---

## 3. Komunikasi Jaringan (*Networking with Dio*)

Konfigurasi Dio wajib memiliki interceptor otomatis:
1. **AuthInterceptor**: Menambahkan header `Authorization: Bearer <token>` dari `flutter_secure_storage`.
2. **IdempotencyInterceptor**: Membuat UUID v4 unik untuk header `X-Idempotency-Key` pada setiap request POST finansial.
3. **ErrorInterceptor**: Menerjemahkan kode HTTP 401/403/422/500 menjadi objek `Failure` dengan pesan bahasa Indonesia yang ramah.
4. **Token Refresh Guard**: Jika menerima 401 Unauthorized, interceptor otomatis mencoba refresh token atau mengarahkan pengguna kembali ke layar Login dengan aman.

---

## 4. Navigasi Terstruktur (*GoRouter*)

Gunakan **`go_router`** dengan rute deklaratif dan *Route Guards*:
* Jika pengguna belum login $\rightarrow$ arahkan ke `/login`.
* Jika pengguna login tetapi status KYC masih pending $\rightarrow$ arahkan ke `/kyc-pending`.
* Jika pengguna aktif $\rightarrow$ izinkan masuk ke `/home` dengan navigasi *StatefulShellRoute* untuk bottom navigation bar yang persisten.

---

## 5. Penyimpanan Lokal (*Local Storage*)

* **Data Kredensial & Token**: Gunakan `flutter_secure_storage` (Keystore di Android, Keychain di iOS).
* **Data Caching Layar**: Gunakan `Hive` atau `SharedPreferences` untuk menyimpan data profil dan ringkasan saldo terakhir agar aplikasi terbuka instan tanpa layar kosong (*offline-first visual response*).

---

## 6. Protokol Implementasi Layar (*Mandatory 1:1 Design Matching*)

Setiap modul tampilan di Flutter **wajib menyesuaikan secara presisi** dengan contoh desain yang diunggah di folder `design/`:

| Fitur Flutter | Lokasi File Flutter | Rujukan Desain Wajib di `design/` |
| :--- | :--- | :--- |
| **Beranda Anggota** | `lib/features/home/presentation/screens/home_screen.dart` | [`design/beranda_anggota/`](file:///Users/yapi/Adi/appdev/koperasi-digital/design/beranda_anggota/) (`screen.png` & `code.html`) |
| **Manajemen Simpanan** | `lib/features/savings/presentation/screens/savings_screen.dart` | [`design/manajemen_simpanan/`](file:///Users/yapi/Adi/appdev/koperasi-digital/design/manajemen_simpanan/) (`screen.png` & `code.html`) |
| **Layanan Pinjaman** | `lib/features/loans/presentation/screens/loans_screen.dart` | [`design/layanan_pinjaman/`](file:///Users/yapi/Adi/appdev/koperasi-digital/design/layanan_pinjaman/) (`screen.png` & `code.html`) |
| **Kopmart Belanja** | `lib/features/kopmart/presentation/screens/kopmart_screen.dart` | [`design/kopmart_belanja_karyawan/`](file:///Users/yapi/Adi/appdev/koperasi-digital/design/kopmart_belanja_karyawan/) (`screen.png` & `code.html`) |
| **Pemindai QRIS** | `lib/features/qris/presentation/screens/qris_scanner_screen.dart` | [`design/bayar_via_qris/`](file:///Users/yapi/Adi/appdev/koperasi-digital/design/bayar_via_qris/) (`screen.png` & `code.html`) |
| **Konfirmasi QRIS** | `lib/features/qris/presentation/screens/qris_confirm_screen.dart` | [`design/konfirmasi_bayar_qris/`](file:///Users/yapi/Adi/appdev/koperasi-digital/design/konfirmasi_bayar_qris/) (`screen.png` & `code.html`) |
| **Bukti Bayar QRIS** | `lib/features/qris/presentation/screens/qris_receipt_screen.dart` | [`design/bukti_pembayaran_qris/`](file:///Users/yapi/Adi/appdev/koperasi-digital/design/bukti_pembayaran_qris/) (`screen.png` & `code.html`) |
| **Mutasi Transaksi** | `lib/features/history/presentation/screens/history_screen.dart` | [`design/riwayat_transaksi_mutasi/`](file:///Users/yapi/Adi/appdev/koperasi-digital/design/riwayat_transaksi_mutasi/) (`screen.png` & `code.html`) |

### Aturan Eksekusi Saat Koding Flutter:
1. **Buka file `code.html`** pada subfolder desain terkait untuk mengecek struktur hierarki tag HTML, CSS kelas, warna hex, dan padding persis yang dipakai.
2. **Buka file `screen.png`** untuk memverifikasi keselarasan visual, proporsi kartu saldo, tata letak ikon, dan tampilan teks.
3. Konversi nilai style ke token `AppColors` dan `AppTypography` yang bersumber dari [`design/kopkar_digital/DESIGN.md`](file:///Users/yapi/Adi/appdev/koperasi-digital/design/kopkar_digital/DESIGN.md).
4. Dilarang mengubah urutan elemen atau susunan komponen di luar contoh yang sudah diunggah.
