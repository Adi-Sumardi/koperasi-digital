<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\LoanInterestType;
use App\Models\LoanProduct;
use Illuminate\Database\Seeder;

class LoanProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Pinjaman Reguler (Bunga Flat)',
                'interest_type' => LoanInterestType::FLAT,
                'annual_interest_rate' => 0.0960, // 0,8% flat/bulan (BRD.md §5.3.2)
                'min_tenor_months' => 3,
                'max_tenor_months' => 36,
                'max_ceiling_amount' => 100_000_000,
                'is_active' => true,
            ],
            [
                'name' => 'Pinjaman Menurun (Bunga Efektif)',
                'interest_type' => LoanInterestType::SLIDING,
                'annual_interest_rate' => 0.0960,
                'min_tenor_months' => 3,
                'max_tenor_months' => 36,
                'max_ceiling_amount' => 100_000_000,
                'is_active' => true,
            ],
        ];

        foreach ($products as $product) {
            LoanProduct::firstOrCreate(['name' => $product['name']], $product);
        }
    }
}
