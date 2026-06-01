<?php

namespace Modules\System\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class SystemServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'system');
        $this->app->register(EventServiceProvider::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'system');

        if (file_exists(__DIR__ . '/../Routes/web.php')) {
            Route::middleware('web')->group(__DIR__ . '/../Routes/web.php');
        }

        if (file_exists(__DIR__ . '/../Routes/api.php')) {
            Route::middleware('api')->prefix('api')->group(__DIR__ . '/../Routes/api.php');
        }
    }
}
