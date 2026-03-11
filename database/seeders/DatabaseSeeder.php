<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // Seulement les seeders essentiels - pas de données fictives
            CleanDatabaseSeeder::class,
            SimGeographySeeder::class,
        ]);
    }
}