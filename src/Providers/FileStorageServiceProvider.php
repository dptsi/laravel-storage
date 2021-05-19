<?php

namespace Dptsi\FileStorage\Providers;

use Dptsi\FileStorage\Helpers\TokenGenerator;
use Dptsi\FileStorage\Core\ManageFile;
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
        $this->app->singleton('file_storage', ManageFile::class);
    }

    protected function publish()
    {
        $this->publishes([
            __DIR__ . '/../config/filestorage.php' => config_path('filestorage.php')
        ]);
    }

    protected function createToken()
    {
        $generator = new TokenGenerator();

        $generator->checkToken();

    }
}