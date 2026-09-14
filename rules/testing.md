# Aturan Pengembangan: Standar Pengujian (*Testing Strategy*)

Dokumen ini merumuskan strategi pengujian otomatis untuk backend Laravel 13 dan aplikasi mobile Flutter Koperasi Digital. Mengingat sistem ini menangani dana anggota, keandalan kalkulasi finansial wajib diuji hingga tingkat matematis eksak.

---

## 1. Standar Pengujian Backend (Pest PHP / PHPUnit)

Target cakupan kode (*code coverage*) minimal adalah **80%**, dengan cakupan **100%** pada seluruh kelas kalkulasi finansial, bunga pinjaman, dan pembukuan jurnal akuntansi.

### 1. Pengujian Kalkulasi Finansial (Unit Test)
Setiap rumus pinjaman wajib diuji dengan berbagai skenario tenor dan nominal:

```php
declare(strict_types=1);

use App\Services\LoanCalculationService;

describe('LoanCalculationService', function () {
    it('calculates flat interest installments correctly', function () {
        $service = new LoanCalculationService();
        
        // Pinjaman: Rp 12.000.000, Tenor: 12 Bulan, Bunga: 0.8% flat per bulan
        $result = $service->calculateFlat(
            principal: 12000000.00,
            tenorMonths: 12,
            monthlyRate: 0.008
        );

        // Pokok per bulan: 1.000.000
        // Bunga per bulan: 96.000
        // Total angsuran per bulan: 1.096.000
        expect($result->monthlyPrincipal)->toEqual(1000000.00)
            ->and($result->monthlyInterest)->toEqual(96000.00)
            ->and($result->totalMonthlyInstallment)->toEqual(1096000.00)
            ->and($result->totalRepayment)->toEqual(13152000.00);
    });

    it('verifies that double-entry journal lines are perfectly balanced', function () {
        $journal = createTestJournalEntry();

        $totalDebit = $journal->lines->sum('debit');
        $totalCredit = $journal->lines->sum('credit');

        expect($totalDebit)->toBeGreaterThan(0)
            ->and($totalDebit)->toEqual($totalCredit);
    });
});
```

### 2. Pengujian Alur Transaksi API (Feature Test)
* Menguji siklus hidup pinjaman: Anggota mengajukan $\rightarrow$ Pengurus menyetujui $\rightarrow$ Bendahara mencairkan $\rightarrow$ Saldo rekening anggota bertambah $\rightarrow$ Jurnal akuntansi terposting.
* Menguji penanganan konkurensi: Menyimulasikan dua permintaan penarikan saldo secara bersamaan untuk memastikan saldo tidak menjadi minus.

---

## 2. Standar Pengujian Mobile (Flutter Test)

1. **Unit Test BLoC (`bloc_test`)**:
   * Pengujian pemindahan state BLoC menggunakan paket `bloc_test`:
     ```dart
     blocTest<SavingsBloc, SavingsState>(
       'emits [SavingsLoadingState, SavingsLoadedState] when FetchSavingsOverviewEvent is added',
       build: () {
         when(() => mockSavingsRepository.getOverview())
             .thenAnswer((_) async => testSavingsOverview);
         return SavingsBloc(savingsRepository: mockSavingsRepository);
       },
       act: (bloc) => bloc.add(FetchSavingsOverviewEvent()),
       expect: () => [
         SavingsLoadingState(),
         SavingsLoadedState(testSavingsOverview),
       ],
     );
     ```
   * Pengujian `CurrencyFormatter`: Memastikan input `1500000` diformat tepat menjadi `Rp 1.500.000`.
2. **Widget Test**:
   * Memastikan `FinancialHeroCard` menyamarkan saldo saat tombol privasi ditekan:
     ```dart
     testWidgets('toggles balance visibility on privacy icon tap', (tester) async {
       await tester.pumpWidget(const TestableWidget(child: FinancialHeroCard(...)));
       
       expect(find.text('Rp 15.000.000'), findsOneWidget);
       
       await tester.tap(find.byIcon(Icons.visibility_rounded));
       await tester.pumpAndSettle();
       
       expect(find.text('Rp ••••••••'), findsOneWidget);
     });
     ```
3. **Integration Test**:
   * Menguji alur lengkap mulai dari layar Login $\rightarrow$ Masuk Dashboard $\rightarrow$ Pilih Simpanan $\rightarrow$ Setor Dana $\rightarrow$ Verifikasi mutasi muncul di riwayat.

---

## 3. Otomasi Pengujian pada CI/CD
* Pengujian dijalankan secara otomatis pada setiap *Pull Request* melalui GitHub Actions.
* PR dilarang di-merge ke branch `main` jika ada satupun test yang gagal (*red build*).
