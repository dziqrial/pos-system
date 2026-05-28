<?php

namespace App\Http\Middleware;

use App\Helpers\Feature;
use Closure;
use Illuminate\Http\Request;

class CheckModuleEnabled
{
    public function handle(Request $request, Closure $next, string $module): mixed
    {
        if (!Feature::enabled($module)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Module not enabled'], 403);
            }
            abort(403, "Module '{$module}' is not enabled for your store.");
        }

        return $next($request);
    }
}
