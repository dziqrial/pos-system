<?php

namespace Modules\System\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\System\Models\Module;
use Modules\System\Models\Permission;
use Modules\System\Models\Role;
use Modules\System\Models\Store;
use Modules\System\Models\StoreModule;
use Modules\System\Models\User;

class SystemSeeder extends Seeder
{
    public function run(): void
    {
        // --- Modules ---
        $modules = [
            ['name' => 'Core',           'key' => 'core',           'version' => '1.0.0', 'is_core' => true],
            ['name' => 'Loyalty',        'key' => 'loyalty',        'version' => '1.0.0', 'is_core' => false],
            ['name' => 'Purchase Order', 'key' => 'purchase_order', 'version' => '1.0.0', 'is_core' => false],
            ['name' => 'FnB',            'key' => 'fnb',            'version' => '1.0.0', 'is_core' => false],
            ['name' => 'Accounting',     'key' => 'accounting',     'version' => '1.0.0', 'is_core' => false],
        ];

        foreach ($modules as $m) {
            Module::firstOrCreate(['key' => $m['key']], $m);
        }

        // --- Default Store ---
        $store = Store::firstOrCreate(
            ['slug' => 'toko-utama'],
            [
                'name'      => 'Toko Utama',
                'type'      => 'retail',
                'is_active' => true,
                'timezone'  => 'Asia/Jakarta',
                'settings'  => ['currency' => 'IDR', 'tax_percent' => 11],
            ]
        );

        // Enable core module for default store
        $coreModule = Module::where('key', 'core')->first();
        StoreModule::firstOrCreate(
            ['store_id' => $store->id, 'module_id' => $coreModule->id],
            ['is_enabled' => true, 'enabled_at' => now()]
        );

        // --- Roles ---
        $ownerRole   = Role::firstOrCreate(['store_id' => $store->id, 'name' => 'owner']);
        $managerRole = Role::firstOrCreate(['store_id' => $store->id, 'name' => 'manager']);
        $kasirRole   = Role::firstOrCreate(['store_id' => $store->id, 'name' => 'kasir']);

        // --- Permissions ---
        $permissionsData = [
            ['name' => 'transaction.create', 'module_key' => 'core',   'description' => 'Buat transaksi'],
            ['name' => 'transaction.void',   'module_key' => 'core',   'description' => 'Void transaksi'],
            ['name' => 'product.create',     'module_key' => 'core',   'description' => 'Buat produk'],
            ['name' => 'product.edit',       'module_key' => 'core',   'description' => 'Edit produk'],
            ['name' => 'product.delete',     'module_key' => 'core',   'description' => 'Hapus produk'],
            ['name' => 'inventory.adjust',   'module_key' => 'core',   'description' => 'Adjustment stok manual'],
            ['name' => 'report.view',        'module_key' => 'core',   'description' => 'Lihat laporan'],
            ['name' => 'user.manage',        'module_key' => 'system', 'description' => 'Kelola user'],
            ['name' => 'role.manage',        'module_key' => 'system', 'description' => 'Kelola peran'],
            ['name' => 'module.toggle',      'module_key' => 'system', 'description' => 'Toggle modul'],
            ['name' => 'outlet.manage',      'module_key' => 'core',   'description' => 'Kelola outlet'],
        ];

        foreach ($permissionsData as $p) {
            Permission::firstOrCreate(['name' => $p['name']], $p);
        }

        // Owner gets all permissions
        $allPermissions = Permission::all();
        $ownerRole->permissions()->sync($allPermissions->pluck('id'));

        // Manager: semua kecuali user.manage, role.manage, module.toggle
        $managerPerms = Permission::whereNotIn('name', ['user.manage', 'role.manage', 'module.toggle'])->get();
        $managerRole->permissions()->sync($managerPerms->pluck('id'));

        // Kasir: hanya transaksi
        $kasirPerms = Permission::whereIn('name', ['transaction.create', 'transaction.void'])->get();
        $kasirRole->permissions()->sync($kasirPerms->pluck('id'));

        // --- Admin User ---
        User::firstOrCreate(
            ['email' => 'admin@pos.test'],
            [
                'store_id'  => $store->id,
                'role_id'   => $ownerRole->id,
                'name'      => 'Administrator',
                'password'  => Hash::make('password'),
                'pin'       => '123456',
                'is_active' => true,
            ]
        );

        $this->command->info('System seeder selesai. Login: admin@pos.test / password');
    }
}
