<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionItemsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $detailItems = [
            // trx id 1
            [
                'id' => 1,
                'transaction_id' => 1,
                'product_id' => 1,
                'qty' => 1,
                'price' => doubleval(23000),
                'total' => doubleval(23000),
                'credit' => doubleval(23000),
                'created_at' => date("Y-m-d H:i:s")
            ],
        ];

        DB::table('transaction_items')->insert($detailItems);

        $this->command->info('Transaction Items table seeded!');
    }
}
