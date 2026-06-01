<?php

namespace Modules\System\Http\Controllers;

use App\Helpers\Feature;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\System\Events\ModuleDisabled;
use Modules\System\Events\ModuleEnabled;
use Modules\System\Models\Module;
use Modules\System\Models\StoreModule;

class ModuleController extends Controller
{
    public function index(): View
    {
        $storeId = auth()->user()->store_id;

        $modules = Module::with(['storeModules' => function ($q) use ($storeId) {
            $q->where('store_id', $storeId);
        }])->get();

        return view('system::modules.index', compact('modules'));
    }

    public function toggle(Request $request, Module $module): RedirectResponse
    {
        if ($module->is_core) {
            return back()->with('error', 'Modul inti tidak dapat dinonaktifkan.');
        }

        $storeId = auth()->user()->store_id;

        $storeModule = StoreModule::firstOrCreate(
            ['store_id' => $storeId, 'module_id' => $module->id],
            ['is_enabled' => false]
        );

        $newStatus = !$storeModule->is_enabled;

        $storeModule->update([
            'is_enabled' => $newStatus,
            'enabled_at' => $newStatus ? now() : null,
        ]);

        Feature::clearCache($storeId, $module->key);

        if ($newStatus) {
            event(new ModuleEnabled($module, $storeId));
        } else {
            event(new ModuleDisabled($module, $storeId));
        }

        $status = $newStatus ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Modul {$module->name} berhasil {$status}.");
    }
}
