<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'id'    => 1,
                'merchant_id' => '1',
                'no_hp' => '081333217810',
                'name' => 'Agil', // Customer
                'password' => Hash::make('Apple8899'),
                'address' => 'Nagoya Point',
                'role' => 'administrator',
                'email' => 'dian.afrial86@gmail.com',
                'created_at' => date("Y-m-d H:i:s"),
            ]
        ];

        DB::table('employees')->insert($data);
    }
}
