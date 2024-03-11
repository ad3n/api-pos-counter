<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SaldosTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'receipt_no' => 'R-' . mt_rand(),
                'merchant_id' => 1,
                'amount' => doubleval(30000),
                'status' => 'ok',
                'type' => 'deposit',
                'payment_method' => 'cash',
                'created_at' => date("Y-m-d H:i:s")
            ],
            [
                'receipt_no' => 'R-' . mt_rand(),
                'merchant_id' => 1,
                'amount' => doubleval(20000),
                'status' => 'ok',
                'type' => 'free',
                'payment_method' => 'cash',
                'created_at' => date("Y-m-d H:i:s")
            ],
        ];

        DB::table('saldos')->insert($data);
        $this->command->info('Saldo table seeded!');
    }
}
