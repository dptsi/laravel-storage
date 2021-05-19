<?php


namespace Dptsi\FileStorage\Facade;


use Illuminate\Support\Facades\Facade;

/**
 * Class FileStorage
 * @package Dptsi\FileStorage\Facade
 * @method static array upload($file_name, $file_ext, $mime_type, $base64_encoded_data)
 * @method static array delete($file_id)
 */

class FileStorage extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'file_storage';
    }
}