<?php


namespace Dptsi\FileStorage\Facade;


use Illuminate\Support\Facades\Facade;

/**
 * Class FileStorage
 * @package Dptsi\FileStorage\Facade
 * @method static mixed upload(\Illuminate\Http\File|\Illuminate\Http\UploadedFile $request)
 * @method static mixed delete(string $file_id)
 * @method static mixed getById(string $file_id)
 * @method static string statusSuccess()
 * @method static string statusError()
 */

class FileStorage extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'file_storage';
    }
}