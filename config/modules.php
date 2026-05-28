<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Module Namespace
    |--------------------------------------------------------------------------
    |
    | Default module namespace.
    |
    */

    'namespace' => 'Modules',

    /*
    |--------------------------------------------------------------------------
    | Module Stubs
    |--------------------------------------------------------------------------
    |
    | Default module stubs.
    |
    */

    'stubs' => [
        'enabled' => false,
        'path'    => base_path('vendor/nwidart/laravel-modules/src/Commands/stubs'),
        'files'   => [
            'routes/web'    => 'Routes/web.php',
            'routes/api'    => 'Routes/api.php',
            'views/index'   => 'Resources/views/index.blade.php',
            'views/master'  => 'Resources/views/layouts/master.blade.php',
            'scaffold/config' => 'Config/config.php',
            'composer'      => 'composer.json',
        ],
        'replacements' => [
            'routes/web'    => ['LOWER_NAME', 'STUDLY_NAME'],
            'routes/api'    => ['LOWER_NAME'],
            'webpack'       => ['LOWER_NAME'],
            'json'          => ['LOWER_NAME', 'STUDLY_NAME', 'MODULE_NAMESPACE', 'PROVIDER_NAMESPACE'],
            'views/index'   => ['LOWER_NAME'],
            'views/master'  => ['LOWER_NAME', 'STUDLY_NAME'],
            'scaffold/config' => ['STUDLY_NAME'],
            'composer'      => [
                'LOWER_NAME',
                'STUDLY_NAME',
                'VENDOR',
                'AUTHOR_NAME',
                'AUTHOR_EMAIL',
                'MODULE_NAMESPACE',
                'PROVIDER_NAMESPACE',
            ],
        ],
        'gitkeep' => true,
    ],

    'paths' => [
        /*
        |--------------------------------------------------------------------------
        | Modules path
        |--------------------------------------------------------------------------
        |
        | This path is used to save the generated module. Set the path to where
        | you want the module to be generated.
        |
        */

        'modules' => base_path('Modules'),

        /*
        |--------------------------------------------------------------------------
        | Modules assets path
        |--------------------------------------------------------------------------
        |
        | Here you may update the modules' assets path.
        |
        */

        'assets' => public_path('modules'),

        /*
        |--------------------------------------------------------------------------
        | The migrations' path
        |--------------------------------------------------------------------------
        |
        | Where you run 'module:publish-migration' command, where do you publish the
        | the migration files?
        |
        */

        'migration' => base_path('database/migrations'),

        /*
        |--------------------------------------------------------------------------
        | The app path
        |--------------------------------------------------------------------------
        |
        | app (Laravel) path
        |
        */

        'app_folder' => 'app/',

        /*
        |--------------------------------------------------------------------------
        | Generator path
        |--------------------------------------------------------------------------
        | Customise the paths where the folders will be generated.
        | Setting the generate key to false will not generate that folder
        |
        */

        'generator' => [
            'config'      => ['path' => 'Config', 'generate' => true],
            'command'     => ['path' => 'Console/Commands', 'generate' => true],
            'migration'   => ['path' => 'Database/Migrations', 'generate' => true],
            'seeder'      => ['path' => 'Database/Seeders', 'generate' => true],
            'factory'     => ['path' => 'Database/Factories', 'generate' => true],
            'model'       => ['path' => 'Models', 'generate' => true],
            'routes'      => ['path' => 'Routes', 'generate' => true],
            'controller'  => ['path' => 'Http/Controllers', 'generate' => true],
            'filter'      => ['path' => 'Http/Middleware', 'generate' => true],
            'request'     => ['path' => 'Http/Requests', 'generate' => true],
            'provider'    => ['path' => 'Providers', 'generate' => true],
            'assets'      => ['path' => 'Resources/assets', 'generate' => false],
            'lang'        => ['path' => 'Resources/lang', 'generate' => false],
            'views'       => ['path' => 'Resources/views', 'generate' => true],
            'test'        => ['path' => 'Tests/Unit', 'generate' => true],
            'test-feature' => ['path' => 'Tests/Feature', 'generate' => true],
            'repository'  => ['path' => 'Repositories', 'generate' => false],
            'event'       => ['path' => 'Events', 'generate' => true],
            'listener'    => ['path' => 'Listeners', 'generate' => true],
            'policies'    => ['path' => 'Policies', 'generate' => false],
            'rules'       => ['path' => 'Rules', 'generate' => false],
            'jobs'        => ['path' => 'Jobs', 'generate' => true],
            'emails'      => ['path' => 'Emails', 'generate' => false],
            'notifications' => ['path' => 'Notifications', 'generate' => false],
            'resource'    => ['path' => 'Transformers', 'generate' => false],
            'component-view' => ['path' => 'Resources/views/components', 'generate' => false],
            'component-class' => ['path' => 'View/Components', 'generate' => false],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Package commands
    |--------------------------------------------------------------------------
    |
    | Here you can define which commands will be visible and used in your
    | application. If for example, you don't use some of the commands, you can
    | just comment them out.
    |
    */

    'commands' => [
        \Nwidart\Modules\Commands\CommandMakeCommand::class,
        \Nwidart\Modules\Commands\ComponentClassMakeCommand::class,
        \Nwidart\Modules\Commands\ComponentViewMakeCommand::class,
        \Nwidart\Modules\Commands\ControllerMakeCommand::class,
        \Nwidart\Modules\Commands\DisableCommand::class,
        \Nwidart\Modules\Commands\DumpCommand::class,
        \Nwidart\Modules\Commands\EnableCommand::class,
        \Nwidart\Modules\Commands\EventMakeCommand::class,
        \Nwidart\Modules\Commands\JobMakeCommand::class,
        \Nwidart\Modules\Commands\ListenerMakeCommand::class,
        \Nwidart\Modules\Commands\MailMakeCommand::class,
        \Nwidart\Modules\Commands\MiddlewareMakeCommand::class,
        \Nwidart\Modules\Commands\MigrateCommand::class,
        \Nwidart\Modules\Commands\MigrateRefreshCommand::class,
        \Nwidart\Modules\Commands\MigrateResetCommand::class,
        \Nwidart\Modules\Commands\MigrateRollbackCommand::class,
        \Nwidart\Modules\Commands\MigrateStatusCommand::class,
        \Nwidart\Modules\Commands\MigrationMakeCommand::class,
        \Nwidart\Modules\Commands\ModelMakeCommand::class,
        \Nwidart\Modules\Commands\ModuleDeleteCommand::class,
        \Nwidart\Modules\Commands\ModuleMakeCommand::class,
        \Nwidart\Modules\Commands\NotificationMakeCommand::class,
        \Nwidart\Modules\Commands\PolicyMakeCommand::class,
        \Nwidart\Modules\Commands\ProviderMakeCommand::class,
        \Nwidart\Modules\Commands\PublishCommand::class,
        \Nwidart\Modules\Commands\PublishConfigurationCommand::class,
        \Nwidart\Modules\Commands\PublishMigrationCommand::class,
        \Nwidart\Modules\Commands\PublishTranslationCommand::class,
        \Nwidart\Modules\Commands\RequestMakeCommand::class,
        \Nwidart\Modules\Commands\ResourceMakeCommand::class,
        \Nwidart\Modules\Commands\RouteProviderMakeCommand::class,
        \Nwidart\Modules\Commands\RuleMakeCommand::class,
        \Nwidart\Modules\Commands\SeedCommand::class,
        \Nwidart\Modules\Commands\SeedMakeCommand::class,
        \Nwidart\Modules\Commands\SetupCommand::class,
        \Nwidart\Modules\Commands\TestMakeCommand::class,
        \Nwidart\Modules\Commands\UnUseCommand::class,
        \Nwidart\Modules\Commands\UseCommand::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Scan Path
    |--------------------------------------------------------------------------
    |
    | Here you define which folder will be scanned. By default will scan vendor
    | directory. This is useful if you host the package in packagist website.
    |
    */

    'scan' => [
        'enabled' => false,
        'paths'   => [
            base_path('vendor/*/*'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Composer File Template
    |--------------------------------------------------------------------------
    |
    | Here is the config for the composer.json file, generated by this package.
    |
    */

    'composer' => [
        'vendor' => 'al',
        'author' => [
            'name'  => 'Al',
            'email' => 'dziqri1103@gmail.com',
        ],
        'composer-output' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    |
    | Here is the config for setting up the caching feature.
    |
    */

    'cache' => [
        'enabled'  => false,
        'driver'   => 'file',
        'key'      => 'laravel-modules',
        'lifetime' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Choose what laravel-modules will register as custom namespaces.
    | Setting one to false will require you to register that part
    | temporarily in your own Service Provider class.
    |--------------------------------------------------------------------------
    */

    'register' => [
        'translations' => true,

        /*
         * Load files on boot or register method
         *
         * Note: boot and register are not the same.
         *       If you have dependencies that need to be resolved before the
         *       module is loaded, use the register method.
         */
        'files' => 'boot',
    ],

    /*
    |--------------------------------------------------------------------------
    | Activators
    |--------------------------------------------------------------------------
    |
    | You can define new types of activators here, full class path
    |
    */

    'activators' => [
        'file' => [
            'class'          => \Nwidart\Modules\Activators\FileActivator::class,
            'statuses-file'  => base_path('.module_statuses.json'),
            'cache-key'      => 'activator.installed',
            'cache-lifetime' => 604800,
        ],
    ],

    'activator' => 'file',

];
