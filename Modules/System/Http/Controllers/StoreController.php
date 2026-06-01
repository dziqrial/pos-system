<?php

namespace Modules\System\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\System\Models\Module;
use Modules\System\Models\Role;
use Modules\System\Models\Store;
use Modules\System\Models\StoreModule;
use Modules\System\Models\User;

class StoreController extends Controller
{
    public function index(): View
    {
        $stores = Store::withCount('users')->latest()->paginate(20);

        return view('system::stores.index', compact('stores'));
    }

    public function create(): View
    {
        return view('system::stores.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'type'           => ['required', 'in:retail,fnb,pharmacy'],
            'timezone'       => ['required', 'timezone'],
            'owner_name'     => ['required', 'string', 'max:255'],
            'owner_email'    => ['required', 'email', 'unique:users,email'],
            'owner_password' => ['required', 'min:8', 'confirmed'],
        ]);

        $store = Store::create([
            'name'      => $validated['name'],
            'slug'      => Str::slug($validated['name']) . '-' . Str::random(4),
            'type'      => $validated['type'],
            'timezone'  => $validated['timezone'],
            'is_active' => true,
            'settings'  => ['currency' => 'IDR', 'tax_percent' => 11],
        ]);

        // Enable core module for new store
        $coreModule = Module::where('key', 'core')->first();
        if ($coreModule) {
            StoreModule::create([
                'store_id'   => $store->id,
                'module_id'  => $coreModule->id,
                'is_enabled' => true,
                'enabled_at' => now(),
            ]);
        }

        // Seed default roles
        $ownerRole = Role::create(['store_id' => $store->id, 'name' => 'owner']);
        Role::create(['store_id' => $store->id, 'name' => 'manager']);
        Role::create(['store_id' => $store->id, 'name' => 'kasir']);

        // Create owner user
        User::create([
            'store_id'  => $store->id,
            'role_id'   => $ownerRole->id,
            'name'      => $validated['owner_name'],
            'email'     => $validated['owner_email'],
            'password'  => Hash::make($validated['owner_password']),
            'is_active' => true,
        ]);

        return redirect()->route('stores.index')
            ->with('success', 'Toko berhasil dibuat.');
    }

    public function edit(Store $store): View
    {
        return view('system::stores.edit', compact('store'));
    }

    public function update(Request $request, Store $store): RedirectResponse
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'type'      => ['required', 'in:retail,fnb,pharmacy'],
            'timezone'  => ['required', 'timezone'],
            'is_active' => ['boolean'],
        ]);

        $store->update($validated);

        return redirect()->route('stores.index')
            ->with('success', 'Toko berhasil diperbarui.');
    }

    public function destroy(Store $store): RedirectResponse
    {
        $store->delete();

        return redirect()->route('stores.index')
            ->with('success', 'Toko berhasil dihapus.');
    }
}
