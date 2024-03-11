<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class MerchantsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run() : void
    {
        $data = [
            [
                'id'    => 1,
                'number' => '#0001',
                'name' => 'Pink Cell', // Customer
                'created_at' => date("Y-m-d H:i:s"),
                'merchant_type' => 'counter_cell',
                'verified' => 1,
                'address' => 'Nagoya Point',
                'user_id' => 1
                /*'country_id' => 'ID',
                'province_id' => '21',
                'regency_id' => '2171'*/
            ]
        ];

        DB::table('merchants')->insert($data);
        $this->command->info('Merchants table seeded!');

    }
}
