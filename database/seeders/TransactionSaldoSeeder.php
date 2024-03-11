<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionSaldoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pivot = [
            [
                'transaction_id' => 1,
                'saldo_id' => 1,
                'amount' => doubleval(10),
                'created_at' => date("Y-m-d H:i:s")
            ],
            [
                'transaction_id' => 2,
                'saldo_id' => 1,
                'amount' => doubleval(10),
                'created_at' => date("Y-m-d H:i:s")
            ]
        ];

        DB::table('transaction_saldos')->insert($pivot);
        $this->command->info('Transaction Saldo table seeded!');
    }
}
