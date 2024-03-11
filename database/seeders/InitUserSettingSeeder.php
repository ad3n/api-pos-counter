<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class InitUserSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')
            ->update([
                'settings' => json_encode($this->initData())
            ]);

    }

    protected function initData()
    {
        return [
            'transaction' => [
                'cost' => 'free'
            ],
            'stock' => [
                'cost'  => 50,
                'min'   => 50
            ]
        ];
    }
}
