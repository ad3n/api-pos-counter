<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
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
                'name' => 'Dian Afrial', //Customer
                'active' => 1,
                'email' => 'dian.afrial86@gmail.com',
                'created_at' => date("Y-m-d H:i:s"),
                'password' => Hash::make(config("global.defaults.password")),
            ]
        ];

        DB::table('users')->insert($data);

        $this->command->info('User table seeded!');

    }
}
