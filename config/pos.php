<?php

return [
    /*
    |--------------------------------------------------------------------------
    | POS Configuration
    |--------------------------------------------------------------------------
    */
    'tax_rate'      => env('POS_TAX_RATE', 0.11),      // 11% PPN
    'service_rate'  => env('POS_SERVICE_RATE', 0.05),  // 5% service charge
    'receipt_prefix'=> env('POS_RECEIPT_PREFIX', 'TRX'),
    'currency'      => env('POS_CURRENCY', 'IDR'),
    'timezone'      => env('POS_TIMEZONE', 'Asia/Jakarta'),
];
