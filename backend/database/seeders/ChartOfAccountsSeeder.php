<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AccountCategory;
use App\Enums\NormalBalance;
use App\Models\ChartOfAccount;
use Illuminate\Database\Seeder;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['code' => '1-1000', 'name' => 'Kas', 'account_type' => AccountCategory::ASSET, 'normal_balance' => NormalBalance::DEBIT],
            ['code' => '2-1001', 'name' => 'Simpanan Pokok Anggota', 'account_type' => AccountCategory::LIABILITY, 'normal_balance' => NormalBalance::CREDIT],
            ['code' => '2-1002', 'name' => 'Simpanan Wajib Anggota', 'account_type' => AccountCategory::LIABILITY, 'normal_balance' => NormalBalance::CREDIT],
            ['code' => '2-1003', 'name' => 'Simpanan Sukarela Anggota', 'account_type' => AccountCategory::LIABILITY, 'normal_balance' => NormalBalance::CREDIT],
            ['code' => '1-1200', 'name' => 'Piutang Pinjaman Anggota', 'account_type' => AccountCategory::ASSET, 'normal_balance' => NormalBalance::DEBIT],
            ['code' => '4-1000', 'name' => 'Pendapatan Bunga Pinjaman', 'account_type' => AccountCategory::REVENUE, 'normal_balance' => NormalBalance::CREDIT],
        ];

        foreach ($accounts as $account) {
            ChartOfAccount::firstOrCreate(['code' => $account['code']], $account);
        }
    }
}
