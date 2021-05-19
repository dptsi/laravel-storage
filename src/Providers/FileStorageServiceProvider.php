<?php

namespace Dptsi\FileStorage\Providers;

use Dptsi\FileStorage\Helpers\TokenGenerator;
use Dptsi\FileStorage\Core\FileStorageManager;
use Illuminate\Support\ServiceProvider;

class FileStorageServiceProvider extends ServiceProvider 
{
    public function boot()
    {
        $this->publish();
        $this->createToken();
    }

    public function register()
    {
        $this->app->singleton('file_storage', FileStorageManager::class);
    }

    protected function publish()
    {
        $this->publishes(
            [
                __DIR__ . '/../config/filestorage.php' => config_path('filestorage.php')
            ],
            'dptsi-storage'
        );
    }

    protected function createToken()
    {
        $generator = new TokenGenerator();

        $generator->checkToken();

    }
}