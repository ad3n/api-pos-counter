<?php
namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // category master
        $data = [
            [
                'id'    => 1,
                'name' => 'Pulsa',
                'created_at' => date("Y-m-d H:i:s")
            ],
            [
                'id'    => 2,
                'name' => 'Paket Data',
                'created_at' => date("Y-m-d H:i:s")
            ],
            [
                'id'    => 3,
                'name' => 'Voucher Fisik',
                'created_at' => date("Y-m-d H:i:s")
            ],
            [
                'id'    => 4,
                'name' => 'SP Paket',
                'created_at' => date("Y-m-d H:i:s")
            ],
            [
                'id'    => 5,
                'name' => 'SP Perdana',
                'created_at' => date("Y-m-d H:i:s")
            ],
            [
                'id'    => 6,
                'name' => 'Topup Game',
                'created_at' => date("Y-m-d H:i:s")
            ],
            [
                'id'    => 7,
                'name' => 'E-Wallet',
                'created_at' => date("Y-m-d H:i:s")
            ],
            [
                'id'    => 8,
                'name' => 'HP Aksesories',
                'created_at' => date("Y-m-d H:i:s")
            ],
        ];

        //Category::factory()->create($data);
        DB::table('categories')->insert($data);
        $this->command->info('Categories table seeded!');

    }
}
