<?php
namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->insert([
            [
                'title' => 'Superadmin',
                'slug' => 'superadmin'
            ],
            [
                'title' => 'Manager',
                'slug' => 'manager'
            ]
        ]);

		// category master
		$data = [
            'id'    => 1,
            'role_id' => 1,
            'name' => 'Dian Afrial R R',
            'email' => 'dian.afrial86@gmail.com',
            'phone' => '081333217810',
            'password' => Hash::make('Apple8899'),
            'created_at' => date("Y-m-d H:i:s"),
		];

		DB::table('supers')->insert([$data]);

        $this->command->info('Admin table seeded!');
    }
}
