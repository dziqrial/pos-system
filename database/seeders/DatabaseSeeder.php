<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            \Modules\System\Database\Seeders\SystemSeeder::class,
            \Modules\Core\Database\Seeders\CoreSeeder::class,
        ]);
    }
}
