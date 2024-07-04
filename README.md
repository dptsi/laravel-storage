# Laravel Storage

A helper package for access ITS file storage API in laravel framework

## Requirements

1. PHP 7.4 or greater
2. Laravel version 8

## Installation

Install using composer:

```shell
composer require dptsi/laravel-storage
```

## example

### Upload

> @method static mixed upload(\Illuminate\Http\File|\Illuminate\Http\UploadedFile $request)

Using form(`\Illuminate\Http\UploadedFile`)

```php
use Dptsi\FileStorage\Facade\FileStorage;

FileStorage::upload($request->file('berkas'))
```

Using local file(`\Illuminate\Http\File`)

```php
use Dptsi\FileStorage\Facade\FileStorage;

FileStorage::upload(new File($path))
```

Success response

```json
{
    "file_ext": "",
    "file_id": "",
    "file_mimetype": "",
    "file_name": "",
    "file_size": ,
    "public_link": "",
    "tag": "",
    "timestamp": "",
},
```

### Delete

> @method static mixed delete(string $file_id)

```php
use Dptsi\FileStorage\Facade\FileStorage;

FileStorage::delete($dokumen->file_id)
```

Success response

```json
{
    "file_ext": "",
    "file_id": "",
    "file_mimetype": "",
    "file_name": "",
    "file_size": ,
    "public_link": "",
    "tag": "",
    "timestamp": "",
},
```

### Check Status

Success
> @method static string statusSuccess()

```php
use Dptsi\FileStorage\Facade\FileStorage;

if($response->status == FileStorage::statusSuccess())
```

Error
> @method static string statusError()

```php
use Dptsi\FileStorage\Facade\FileStorage;

if($response->status == FileStorage::statusError())
```

### AWS
Make sure your aws config exist on filestorage.php
```php
    'aws_key'                   => env('AWS_ACCESS_KEY_ID'),
    'aws_secret'                => env('AWS_SECRET_ACCESS_KEY'),
    'aws_region'                => env('AWS_DEFAULT_REGION'),
    'aws_bucket'                => env('AWS_BUCKET'),
```
### Upload to Aws

> @method static mixed awsUpload(\Illuminate\Http\File|\Illuminate\Http\UploadedFile $request, string $subdirectory = null)

Using form(`\Illuminate\Http\UploadedFile`)
Using the optional parameter sub-directory to make files uploaded to a sub-directory instead of the root directory. File ID on AWS S3 use name of uploaded file instead generate uuid for that file, so make sure filename is unique.

```php
use Dptsi\FileStorage\Facade\FileStorage;

FileStorage::awsUpload($request->file('berkas'), 'images')
//or
FileStorage::awsUpload($request->file('berkas'), 'assets/images')

```

### Make Temporary public link from Aws S3 
You can use temporary public uri with 
> @method static mixed awsGetTemporaryPublicLink(string $aws_file_id, DateTime $datetime = null)

```php
use Dptsi\FileStorage\Facade\FileStorage;

FileStorage::awsGetTemporaryPublicLink('fileid.pdf', Carbon::now->addMinutes(5))

```

Using fileid string and optional Datetime for how long the url can be used, by default the url can be used for 30 minutes.

### Get File From Aws
You can get file from aws S3 storage with 
> @method static mixed awsGetFileById(string $aws_file_id)

```php
use Dptsi\FileStorage\Facade\FileStorage;

FileStorage::awsGetFileById('aws_file_id');

```
### Google Storage
Make sure your google config exist on filestorage.php
```php
    'gcs_key_path'              => env('GOOGLE_KEY_PATH'),
    'gcs_project_id'            => env('GOOGLE_PROJECT_ID'),
    'gcs_bucket'                => env('GOOGLE_BUCKET'),
```

### Upload to Google Storage
 > @method static mixed gcsUpload(\Illuminate\Http\File|\Illuminate\Http\UploadedFile $data, string $subdirectory = null, string $bucketname = null, string $projectId = null)
Using form(`\Illuminate\Http\UploadedFile` | `\Illuminate\Http\File`)
Using the optional parameter sub-directory to make files uploaded to a sub-directory instead of the root directory. File ID on Google Storage use name of uploaded file instead generate uuid for that file, so make sure filename is unique.

```php
use Dptsi\FileStorage\Facade\FileStorage;

FileStorage::gcsUpload($request->file('berkas'), 'images')
//or
FileStorage::gcsUpload($request->file('berkas'), 'assets/images')
```

### Delete from Google Storage
 > @method static mixed gcsDelete(string $gcs_file_id, string $bucketname = null, string $projectId = null)

```php
use Dptsi\FileStorage\Facade\FileStorage;

FileStorage::gcsDelete('gcs_file_id')
```

### Get File by ID from Google Storage
 > @method static mixed gcsGetFileById(string $gcs_file_id, string $bucketname = null, string $projectId = null)
 Return base64string data and metadata of the object
 ```php
use Dptsi\FileStorage\Facade\FileStorage;

FileStorage::gcsGetFileById('gcs_file_id')
```
### Download File To Local Storage
 > @method static mixed gcsDownloadFile(string $gcs_file_id, string $savepath, string $bucketname = null, string $projectId = null)
 File downloaded into system directory
  ```php
use Dptsi\FileStorage\Facade\FileStorage;

FileStorage::gcsDownloadFile('gcs_file_id', storage_path('temp/image.png'))
```

### Get File as Binary String
 > @method static mixed gcsGetFileByIdAsString(string $gcs_file_id, string $bucketname = null, string $projectId = null)
Return object
```php
{# ▼
  +"status": "OK"
  +"string_data": bynary_string_data
}
```
example
```php
use Dptsi\FileStorage\Facade\FileStorage;

FileStorage::gcsGetFileByIdAsString('gcs_file_id')
```

### Get File as Binary Stream
 > @method static mixed gcsGetFileByIdAsStream(string $gcs_file_id, string $bucketname = null, string $projectId = null)
 Return object
```php
{# ▼
  +"status": "OK"
  +"stream_data": GuzzleHttp\Psr7\Stream
}
```
example
```php
use Dptsi\FileStorage\Facade\FileStorage;

FileStorage::gcsGetFileByIdAsStream('gcs_file_id')
```

### Make Temporary public link from Google Storage
You can use temporary public uri with 
 > @method static mixed gcsGetTemporaryPublicLink(string $gcs_file_id, DateTime $datetime = null, string $bucketname = null, string $projectId = null)
 Return object
```php
{# ▼
  +"status": "OK"
  +"expired_at": "xxxxxxxxxxxxx"
  +"url": "xxxxxxxxxxxxx"
}
```
