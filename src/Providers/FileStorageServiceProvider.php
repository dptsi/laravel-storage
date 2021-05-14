<?php

namespace Dptsi\FileStorage\Providers;

use Dptsi\FileStorage\Core\FileStorage;
use Illuminate\Support\ServiceProvider;

class FileStorageServiceProvider extends ServiceProvider 
{

    public function boot()
    {
        $this->publish();
    }

    public function register()
    {
        $this->app->singleton('file_storage', FileStorage::class);
    }

    protected function publish()
    {
        $this->publishes([
            __DIR__ . '/../config/filestorage.php' => config_path('filestorage.php')
        ]);
    }
}