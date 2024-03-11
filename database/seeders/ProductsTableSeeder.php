<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductsTableSeeder extends Seeder
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
                'id'            => 1,
                'name'          => '3Gb / 5Hr',
                'merchant_id'   => 1,
                'brand_id'      => 1,
                'regular_price' => doubleval(15000),
                'created_at'    => date("Y-m-d H:i:s")
            ]
        ];

        DB::table('products')->insert($data);
        $this->command->info('Products table seeded!');
    }
}
