<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
        public function run(): void
        {
                $this->call(UserSeeder::class);
                $this->call(UnitSeeder::class);
                $this->call(WorkCategorySeeder::class);
                $this->call(WorkTypeSeeder::class);
                $this->call(MaterialSpecSeeder::class);
                $this->call(TariffSeeder::class);
                $this->call(ShipSeeder::class);
        }
}
