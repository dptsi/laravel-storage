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

## Usage

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

### Upload to Aws

> @method static mixed awsUpload(\Illuminate\Http\File|\Illuminate\Http\UploadedFile $request, string $subdirectory = null)

Using form(`\Illuminate\Http\UploadedFile`)
Using the optional parameter sub-directory to make files uploaded to a sub-directory instead of the root directory

```php
use Dptsi\FileStorage\Facade\FileStorage;

FileStorage::awsUpload($request->file('berkas'), 'images')
//or
FileStorage::awsUpload($request->file('berkas'), 'assets/images')

```

You can use temporary public uri with 
> @method static mixed awsGetTemporaryPublicLink(string $aws_file_id, DateTime $datetime = null)

```php
use Dptsi\FileStorage\Facade\FileStorage;

FileStorage::awsGetTemporaryPublicLink('fileid.pdf', Carbon::now->addMinutes(5))

```

Using fileid string and optional Datetime for how long the url can be used, by default the url can be used for 30 minutes.