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

];
