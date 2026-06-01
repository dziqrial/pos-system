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
        $categories = [
            ['name' => 'Makanan',  'slug' => 'makanan',  'parent_id' => null],
            ['name' => 'Minuman',  'slug' => 'minuman',  'parent_id' => null],
            ['name' => 'Snack',    'slug' => 'snack',    'parent_id' => null],
            ['name' => 'Lainnya',  'slug' => 'lainnya',  'parent_id' => null],
        ];

        foreach ($categories as $cat) {
            DB::table('categories')->insert([
                'store_id'   => $storeId,
                'name'       => $cat['name'],
                'slug'       => $cat['slug'],
                'parent_id'  => $cat['parent_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
