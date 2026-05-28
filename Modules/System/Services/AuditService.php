<?php

namespace Modules\System\Services;

use Illuminate\Http\Request;
use Modules\System\Models\AuditLog;

class AuditService
{
    public static function log(string $action, array $data = [], ?Request $request = null): void
    {
        $user = auth()->user();

        AuditLog::create([
            'user_id'    => $user?->id,
            'store_id'   => $user?->store_id,
            'action'     => $action,
            'model_type' => $data['model_type'] ?? null,
            'model_id'   => $data['model_id'] ?? null,
            'old_values' => $data['old_values'] ?? null,
            'new_values' => $data['new_values'] ?? null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'created_at' => now(),
        ]);
    }
}
