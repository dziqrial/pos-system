<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CoreSeeder extends Seeder
{
    public function run(): void
    {
        $storeId = DB::table('stores')->first()?->id;

        if (!$storeId) {
            return;
        }

        // Create a default outlet
        DB::table('outlets')->insert([
            'store_id'   => $storeId,
            'name'       => 'Outlet Utama',
            'address'    => null,
            'phone'      => null,
            'is_active'  => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create default categories
        $categories = ['Makanan', 'Minuman', 'Snack', 'Lainnya'];

        foreach ($categories as $name) {
            DB::table('categories')->insert([
                'store_id'   => $storeId,
                'name'       => $name,
                'parent_id'  => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
