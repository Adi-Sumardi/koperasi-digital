<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Nominal Simpanan (BRD.md §5.2)
    |--------------------------------------------------------------------------
    */

    'simpanan_pokok_amount' => (float) env('KOPERASI_SIMPANAN_POKOK_AMOUNT', 1_000_000),
    'simpanan_wajib_amount' => (float) env('KOPERASI_SIMPANAN_WAJIB_AMOUNT', 100_000),
    'simpanan_sukarela_minimum' => (float) env('KOPERASI_SIMPANAN_SUKARELA_MINIMUM', 10_000),

    /*
    |--------------------------------------------------------------------------
    | Kebijakan Pinjaman (BRD.md §5.3)
    |--------------------------------------------------------------------------
    */

    'loan' => [
        // Plafon Maksimal = ceiling_multiplier x total simpanan (Pokok+Wajib+Sukarela).
        'ceiling_multiplier' => (float) env('KOPERASI_LOAN_CEILING_MULTIPLIER', 3),

        // Total angsuran bulanan tidak boleh melebihi rasio ini dari gaji pokok bulanan.
        'dsr_max_ratio' => (float) env('KOPERASI_LOAN_DSR_MAX_RATIO', 0.35),

        // Jenjang persetujuan berjenjang berdasarkan nominal pengajuan (BRD.md §5.3.3).
        // 'max_amount' null berarti tanpa batas atas (tier tertinggi).
        'approval_tiers' => [
            ['max_amount' => 5_000_000, 'required_approvals' => 1, 'roles' => ['treasurer', 'chairman', 'superadmin']],
            ['max_amount' => 25_000_000, 'required_approvals' => 2, 'roles' => ['treasurer', 'chairman', 'superadmin']],
            ['max_amount' => null, 'required_approvals' => 2, 'roles' => ['chairman', 'superadmin']],
        ],
    ],

];
