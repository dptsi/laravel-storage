<?php

namespace Dptsi\FileStorage\Providers;

use Dptsi\FileStorage\Core\FileStorage;

use Illuminate\Http\Testing\File;
use Illuminate\Support\ServiceProvider;

class FileStorageServiceProvider extends ServiceProvider 
{

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/filestorage.php' => config_path('filestorage.php')
        ]);
    }

    public function register()
    {
        $this->app->singleton(FileStorage::class, function(){
            return new FileStorage();
        });
    }
}