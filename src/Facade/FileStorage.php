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
 * @method static mixed awsUpload(\Illuminate\Http\File|\Illuminate\Http\UploadedFile $request, string $subdirectory = null, string $bucketname = null)
 * @method static mixed awsDelete(string $aws_file_id, string $bucketname = null)
 * @method static mixed awsGetFileById(string $aws_file_id, string $bucketname = null)
 * @method static mixed awsDownloadFile(string $aws_file_id, string $bucketname = null, string $savepath)
 * @method static mixed awsGetTemporaryPublicLink(string $aws_file_id, DateTime $datetime = null, string $bucketname = null)
 * @method static mixed gcsUpload(\Illuminate\Http\File|\Illuminate\Http\UploadedFile $data, string $subdirectory = null, string $bucketname = null, string $projectId = null)
 * @method static mixed gcsDelete(string $gcs_file_id, string $bucketname = null, string $projectId = null)
 * @method static mixed gcsGetFileById(string $gcs_file_id, string $bucketname = null, string $projectId = null)
 * @method static mixed gcsDownloadFile(string $gcs_file_id, string $savepath, string $bucketname = null, string $projectId = null)
 * @method static mixed gcsGetFileByIdAsString(string $gcs_file_id, string $bucketname = null, string $projectId = null)
 * @method static mixed gcsGetFileByIdAsStream(string $gcs_file_id, string $bucketname = null, string $projectId = null)
 * @method static mixed gcsGetTemporaryPublicLink(string $gcs_file_id, DateTime $datetime = null, string $bucketname = null, string $projectId = null)
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
