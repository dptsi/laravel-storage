<?php


namespace Dptsi\FileStorage\Facade;


use Illuminate\Support\Facades\Facade;

/**
 * Class FileStorage
 * @package Dptsi\FileStorage\Facade
 * @method static array upload(\Illuminate\Http\UploadedFile $request)
 * @method static array delete(string $file_id)
 */

class FileStorage extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'file_storage';
    }
}