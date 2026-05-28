<?php

namespace App\Providers;

use App\Helpers\Feature;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Create SQLite database file if it doesn't exist
        $dbPath = database_path('database.sqlite');
        if (!file_exists($dbPath)) {
            touch($dbPath);
        }

        // @moduleEnabled('key') ... @endmoduleEnabled
        Blade::if('moduleEnabled', function (string $module) {
            return Feature::enabled($module);
        });

        // @moduleDisabled('key') ... @endmoduleDisabled
        Blade::if('moduleDisabled', function (string $module) {
            return !Feature::enabled($module);
        });
    }
}
