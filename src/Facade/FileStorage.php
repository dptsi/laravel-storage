<?php


namespace Dptsi\FileStorage\Facade;


use Illuminate\Support\Facades\Facade;

/**
 * Class FileStorage
 * @package Dptsi\FileStorage\Facade
 * @method static mixed uploadBase64File($filename, $extension, $mimetype, $base64file)
 * @method static mixed upload(\Illuminate\Http\File|\Illuminate\Http\UploadedFile $request)
 * @method static mixed delete(string $file_id)
 * @method static mixed getFileById(string $file_id)
 * @method static mixed awsUpload(\Illuminate\Http\File|\Illuminate\Http\UploadedFile $request, string $subdirectory = null)
 * @method static mixed awsDelete(string $aws_file_id)
 * @method static mixed awsGetFileById(string $aws_file_id)
 * @method static mixed awsGetTemporaryPublicLink(string $aws_file_id, DateTime $datetime = null)
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
