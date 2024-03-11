<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'id'    => 1,
                'name' => 'TSEL', // Customer
                'created_at' => date("Y-m-d H:i:s")
            ],
            [
                'id'    => 2,
                'name' => 'XL', // Customer
                'created_at' => date("Y-m-d H:i:s")
            ],
            [
                'id'    => 3,
                'name' => 'Indosat', // Customer
                'created_at' => date("Y-m-d H:i:s")
            ],
            [
                'id'    => 4,
                'name' => 'Three', // Customer
                'created_at' => date("Y-m-d H:i:s")
            ],
            [
                'id'    => 5,
                'name' => 'Smartfren', // Customer
                'created_at' => date("Y-m-d H:i:s")
            ],
            [
                'id'    => 6,
                'name' => 'Axis', // Customer
                'created_at' => date("Y-m-d H:i:s")
            ],
            [
                'id'    => 7,
                'name' => 'Dana', // Customer
                'created_at' => date("Y-m-d H:i:s")
            ],
            [
                'id'    => 8,
                'name' => 'Ovo', // Customer
                'created_at' => date("Y-m-d H:i:s")
            ],
            [
                'id'    => 9,
                'name' => 'Maxim', // Customer
                'created_at' => date("Y-m-d H:i:s")
            ],
            [
                'id'    => 10,
                'name' => 'ShopeePay', // Customer
                'created_at' => date("Y-m-d H:i:s")
            ],
            [
                'id'    => 11,
                'name' => 'GrabPay', // Customer
                'created_at' => date("Y-m-d H:i:s")
            ]
        ];

        DB::table('brands')->insert($data);
    }
}
