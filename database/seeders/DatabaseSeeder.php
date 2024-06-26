<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            InitEmployeeSeeder::class,
            InitUserSettingSeeder::class,
            BrandSeeder::class,
            ProviderTableSeeder::class,
            ProductsTableSeeder::class,
            CategoriesTableSeeder::class,
            SaldosTableSeeder::class,
            TransactionsTableSeeder::class,
            TransactionSaldoSeeder::class,
        ]);
    }
}
