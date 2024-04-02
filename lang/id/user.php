<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Register Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'complete_data'     => "Profil berhasil dimutakhirkan",
    'complete_merchant'  => "Data lapak Anda berhasil dimutakhirkan",
    "success_add_product" => 'Berhasil tambah produk menu',
    "success_edit_product" => 'Berhasil update produk',
    "success_delete_product" => 'Berhasil hapus produk menu',
    "success_activation_product" => 'Berhasil update',
    "model_not_found" => 'Model ID tidak dapat ditemukan',

    "defaults" => [
        "order_name" => 'Transaksi No :no'
    ],

    "merchant_type" => [
        'street_food'   => 'Kaki5 ( Street Food )',
        'resto'         => 'Resto',
        'grocery_store'      => 'Toko',
        'cafe'          => 'Cafe',
        'laundry'       => 'Laundry'
    ],

    "payment_methods" => [
        'cash'               => "Tunai",
        'credit_card'        => 'Credit Card',
        'debit_card'         => 'Debit Card',
        'bank_transfer'      => 'Bank Transafer',
        'bonus'              => 'Free Bonus',
        'dana'               => 'Dana',
        'bca'                => 'BCA'
    ],

    "payment_statuses" => [
        'paid'      => 'Lunas',
        'credit'    => 'Piutang'
    ],

    "saldo_type" => [
        'free'  => 'Free',
        'deposit' => 'Deposit',
        'referral_bonus'  => 'Referral Bonus'
    ],

    "transaction_type" => [
        'income' => 'Income',
        'expense' => 'Expense'
    ],

    "expense_type" => [
        'tarik_tunai'     => 'Tarik Tunai',
        'belanja'         => 'Belanja',
        'pinjaman'         => 'Pinjaman',
        'kasbon'         => 'Kasbon'
    ],
];
