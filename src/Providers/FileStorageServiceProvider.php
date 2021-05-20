<?php

namespace Dptsi\FileStorage\Providers;

use Dptsi\FileStorage\Core\FileStorageManager;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class FileStorageServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publish();
    }

    public function register()
    {
        $this->app->singleton(
            'file_storage',
            function () {
                $storage = new FileStorageManager();

                $storage->onGenerateToken(
                    function (string $token) {
                        Cache::put('access_token', $token, $seconds = 3550);
                    }
                );

                $storage->onRequestToken(
                    function () {
                        return Cache::get('access_token');
                    }
                );

                $storage->onCheckToken(
                    function () {
                        return Cache::has('access_token');
                    }
                );

                return $storage;
            }
        );
    }

    protected function publish()
    {
        $this->publishes(
            [
                __DIR__ . '/../config/filestorage.php' => config_path('filestorage.php'),
            ],
            'dptsi-storage'
        );
    }
}