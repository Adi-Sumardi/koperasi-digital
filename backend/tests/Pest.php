<?php

use App\Actions\Savings\CreateMemberSavingsAccountsAction;
use App\Enums\KycStatus;
use App\Enums\SavingsType;
use App\Models\Member;
use App\Models\User;
use Database\Seeders\ChartOfAccountsSeeder;
use Database\Seeders\LoanProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

// Modul akuntansi/simpanan butuh Chart of Accounts (Kas, Simpanan Pokok/Wajib/Sukarela)
// sebelum JournalPostingService bisa memposting jurnal berpasangan.
uses()
    ->beforeEach(fn () => test()->seed(ChartOfAccountsSeeder::class))
    ->in('Feature/Savings');

uses()
    ->beforeEach(fn () => test()->seed([ChartOfAccountsSeeder::class, LoanProductSeeder::class]))
    ->in('Feature/Loans');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

function memberWithSavingsAccounts(): Member
{
    $member = Member::factory()->for(User::factory())->create();

    app(CreateMemberSavingsAccountsAction::class)->execute($member);

    return $member->refresh();
}

/**
 * Anggota AKTIF dengan saldo simpanan & gaji tertentu, siap untuk pengajuan pinjaman.
 */
function activeMemberWithSavings(float $totalSavingsSukarela = 5_000_000, float $monthlySalary = 10_000_000): Member
{
    $member = Member::factory()->active()->create(['monthly_salary' => $monthlySalary]);

    app(CreateMemberSavingsAccountsAction::class)->execute($member);

    $member->savingsAccounts()->where('type', SavingsType::SUKARELA)->update(['balance' => $totalSavingsSukarela]);

    $member->kyc()->create([
        'ktp_photo_path' => 'kyc/test/ktp.jpg',
        'selfie_ktp_path' => 'kyc/test/selfie.jpg',
        'status' => KycStatus::APPROVED,
        'verified_at' => now(),
    ]);

    return $member->refresh();
}
