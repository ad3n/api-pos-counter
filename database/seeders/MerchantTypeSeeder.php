<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MerchantTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // master data
        $data = [
            [
                'code'          => 'counter_cell',
                'name'          => 'Pulsa Counter',
                'created_at'    => date("Y-m-d H:i:s")
            ],
            [
                'code'          => 'cafe',
                'name'          => 'Cafe',
                'created_at'    => date("Y-m-d H:i:s")
            ],
            [
                'code'          => 'laundry',
                'name'          => 'Laundry',
                'created_at'    => date("Y-m-d H:i:s")
            ]
        ];

        DB::table('merchant_types')->insert($data);
        $this->command->info('Merchants type table seeded!');
    }
}
