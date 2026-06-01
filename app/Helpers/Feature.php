<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Feature
{
    public static function enabled(string $moduleKey): bool
    {
        try {
            $user    = Auth::user();
            $storeId = $user?->store_id;

            if (!$storeId) {
                return false;
            }

            $cacheKey = "module_enabled_{$storeId}_{$moduleKey}";

            return Cache::remember($cacheKey, 300, function () use ($storeId, $moduleKey) {
                $module = DB::table('modules')->where('key', $moduleKey)->first();

                if (!$module) {
                    return false;
                }

                if ($module->is_core) {
                    return true;
                }

                return DB::table('store_modules')
                    ->where('store_id', $storeId)
                    ->where('module_id', $module->id)
                    ->where('is_enabled', true)
                    ->exists();
            });
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function clearCache(?int $storeId = null, ?string $moduleKey = null): void
    {
        if ($storeId && $moduleKey) {
            Cache::forget("module_enabled_{$storeId}_{$moduleKey}");
        }
    }
}
