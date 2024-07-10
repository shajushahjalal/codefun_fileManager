<?php

namespace CodeFun\FileManager\App\Providers;

use Illuminate\Support\ServiceProvider;

class FileManagerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/FileManager.php', 'FileManager'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        /**
         * Published Files
         */
        $this->publishes([
            __DIR__.'/../../database/migrations' => database_path('2023_01_01_000000_create_file_managers_table.php'),

        ], "codefun_fileManager_migration");
        
        $this->publishes([
            __DIR__.'/../../config' => config_path('FileManager.php'),

        ], "codefun_fileManager_config");
    }
}
