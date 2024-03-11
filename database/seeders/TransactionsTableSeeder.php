<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $item = [
            [
                'id' => 1,
                'order_no' => 'K-' . mt_rand(),
                'merchant_id' => 1,
                'work_date' => '2024-02-20',
                'status' => 'success',
                'type' => 'income',
                'payment_method' => 'cash',
                'payment_status' => 'paid',
                'paid_at' => date("Y-m-d H:i:s"),
                'created_at' => date("Y-m-d H:i:s")
            ],
            [
                'id' => 2,
                'order_no' => 'K-' . mt_rand(),
                'merchant_id' => 1,
                'work_date' => '2024-02-20',
                'status' => 'success',
                'type' => 'income',
                'payment_method' => 'cash',
                'payment_status' => 'paid',
                'paid_at' => date("Y-m-d H:i:s"),
                'created_at' => date("Y-m-d H:i:s")
            ]
        ];

        DB::table('transactions')->insert($item);
    }
}
