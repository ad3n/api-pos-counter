<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Global Defaults
    |--------------------------------------------------------------------------
    |
    | This option controls the default authentication "guard" and password
    | reset options for your application. You may change these defaults
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'password' => 'VCHYtfBuTWGp',
    ],

    'prefixes' => [
        'transaction'   => 'K-',
        'saldo'         => 'R-'
    ],

    'cart' => [
        'driver'            => 'file',
        'create_expiry'     => 120, // in minutes
        'put_expiry'        => 120 // in minuts
    ],

    'date' => [
        'driver'            => env('CACHE_DRIVER'),
        'long_expiry'       => 1320,
        'aet_close'         => env('HOURS_AET_CLOSE', 5),
    ],

    'transaction' => [
        'cost'              => env('SALDO_CHARGE', 10),
        'count_items_max'   => env('SALDO_CHARGE_MAX_ITEMS', 1),
    ],

    'pricing' => [
        'transaction' => 'free'
    ],

    'saldo' => [
        'driver'            => env('CACHE_DRIVER'),
        'create_expiry'     => 15, // in minutes
        'put_expiry'        => 15, // in minuts
        'warning_usage'     => 30, // multiplier
        'danger_usage'      => 0
    ],

    'free_trial' => [
        'cost'          => env('SALDO_BONUS_AMOUNT', 10000),
        'expiry_date'   => env('SALDO_BONUS_EXPIRY_DATE', '2019-02-28'),
        'user_max'      => 100
    ],

    'employee' => [
        'default_id' => 1,
        'roles' => [
            'staff',
            'manager',
            'administrator'
        ]
    ],

    "device_no" => [
        "enable_forbidden" => false,
        "forbidden" => [
            "universal7870",
            "msm8953",
            "msm8916",
            "SC7730SE"
        ]
    ]

];
