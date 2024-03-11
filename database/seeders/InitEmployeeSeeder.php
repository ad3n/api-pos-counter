<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InitEmployeeSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            UsersTableSeeder::class,
            AdminTableSeeder::class,
            MerchantTypeSeeder::class,
            MerchantsTableSeeder::class
        ]);

        $this->command->info('Main tables seeded!');

        DB::unprepared(file_get_contents(database_path('factories/countries.sql')));
        $this->command->info('Country table seeded!');

        DB::unprepared(file_get_contents(database_path('factories/provinces.sql')));
        $this->command->info('Province table seeded!');

        DB::unprepared(file_get_contents(database_path('factories/regencies.sql')));
        $this->command->info('Regency table seeded!');
    }
}
