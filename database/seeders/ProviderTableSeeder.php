<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProviderTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // master data
        $data = [
            [
                'name'          => 'Shopee Merchant',
                'created_at'    => date("Y-m-d H:i:s")
            ],
            [
                'name'          => 'Orderkuota',
                'created_at'    => date("Y-m-d H:i:s")
            ],
            [
                'name'          => 'Digipos',
                'created_at'    => date("Y-m-d H:i:s")
            ],
            [
                'name'          => 'I-Simple',
                'created_at'    => date("Y-m-d H:i:s")
            ],
            [
                'name'          => 'XL Dompul',
                'created_at'    => date("Y-m-d H:i:s")
            ],
            [
                'name'          => 'BCA',
                'created_at'    => date("Y-m-d H:i:s")
            ],
            [
                'name'          => 'Flip',
                'created_at'    => date("Y-m-d H:i:s")
            ],
        ];

        DB::table('providers')->insert($data);
        $this->command->info('Provider table seeded!');
    }
}
