<?php

namespace Dptsi\FileStorage\Core;

use Closure;
use Dptsi\FileStorage\Exception\InvalidArgument;
use Dptsi\FileStorage\Exception\ServerFailure;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Google\Cloud\Core\Exception\GoogleException;
use DateTime;
use Carbon\Carbon;
use Google\Cloud\Storage\StorageClient;
use stdClass;

class FileStorageManager
{
    public const STATUS_SUCCESS = 'OK';
    public const STATUS_ERROR = 'ERR';
    private Closure $generate_token_callback;
    private Closure $request_token_callback;
    private Closure $check_token_callback;
    private int $max_retry = 3;

    public function statusSuccess()
    {
        return self::STATUS_SUCCESS;
    }

    public function statusError()
    {
        return self::STATUS_ERROR;
    }

    public function onGenerateToken(Closure $callback)
    {
        $this->generate_token_callback = $callback;
    }

    public function onRequestToken(Closure $callback)
    {
        $this->request_token_callback = $callback;
    }

    public function onCheckToken(Closure $callback)
    {
        $this->check_token_callback = $callback;
    }

    public function uploadBase64File($filename, $extension, $mimetype, $base64file){
        if(empty($filename) || empty($extension) || empty($mimetype) || empty($base64file)){
            throw new InvalidArgument('Unsupported argument type.');
        }

        $this->ensureTokenAvailable();

        $attempts = 0;

        $response = null;

        do {
            try {
                $client = new Client(
                    [
                        'base_uri' => config('filestorage.host_uri'),
                    ]
                );

                $data['headers'] = [
                    'x-code' => $this->getToken(),
                    'x-client-id' => config('filestorage.client_id'),
                    'Content-Type' => 'application/json',
                ];

                $data['body'] = json_encode(
                    [
                        'file_name' => $filename,
                        'file_ext' => $extension,
                        'mime_type' => $mimetype,
                        'binary_data_b64' => $base64file,
                    ]
                );

                $response = $client->post('/d/files', $data);
            } catch (ServerException $e) {
                // Handle exception lain dari API
                $attempts++;
                $this->generateToken();
                continue;
            }

            break;
        } while ($attempts < $this->max_retry);

        if (!$response) {
            throw new ServerFailure('Server error.');
        }

        return json_decode($response->getBody()->getContents());
    }

    public function upload($request)
    {
        if($request instanceof UploadedFile) {
            $filename_extension = $request->getClientOriginalName();

            $filename = pathinfo($filename_extension, PATHINFO_FILENAME);
            $filename = preg_replace("/[^a-zA-Z0-9]+/", "", $filename);
            if ($filename == '') {
                $filename = 'undefined' . time();
            }

            $extension = pathinfo($filename_extension, PATHINFO_EXTENSION);

            $b64 = base64_encode(file_get_contents($request));
        } elseif ($request instanceof File) {
            $filename = pathinfo($request->getRealPath(), PATHINFO_FILENAME);
            $filename = preg_replace("/[^a-zA-Z0-9]+/", "", $filename);
            if ($filename == '') {
                $filename = 'undefined' . time();
            }

            $extension = pathinfo($request->getRealPath(), PATHINFO_EXTENSION);

            $b64 = base64_encode(file_get_contents($request));
        } else {
            throw new InvalidArgument('Unsupported argument type.');
        }

        $this->ensureTokenAvailable();

        $attempts = 0;

        $response = null;

        do {
            try {
                $client = new Client(
                    [
                        'base_uri' => config('filestorage.host_uri'),
                    ]
                );

                $data['headers'] = [
                    'x-code' => $this->getToken(),
                    'x-client-id' => config('filestorage.client_id'),
                    'Content-Type' => 'application/json',
                ];

                $data['body'] = json_encode(
                    [
                        'file_name' => $filename,
                        'file_ext' => $extension,
                        'mime_type' => $request->getMimeType(),
                        'binary_data_b64' => $b64,
                    ]
                );

                $response = $client->post('/d/files', $data);
            } catch (ServerException $e) {
                // Handle exception lain dari API
                $attempts++;
                $this->generateToken();
                continue;
            }

            break;
        } while ($attempts < $this->max_retry);

        if (!$response) {
            throw new ServerFailure('Server error.');
        }

        return json_decode($response->getBody()->getContents());
    }

    private function ensureTokenAvailable(): void
    {
        if (!$this->hasToken()) {
            $this->generateToken();
        }
    }

    private function getToken(): string
    {
        return ($this->request_token_callback)();
    }

    private function hasToken(): bool
    {
        return ($this->check_token_callback)();
    }

    private function generateToken(): void
    {
        $client = new Client(
            [
                'base_uri' => config('filestorage.authorization_server_uri'),
            ]
        );
        $data['headers'] = [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];
        $data['form_params'] = [
            'grant_type' => 'client_credentials',
            'client_id' => config('filestorage.client_id'),
            'client_secret' => config('filestorage.client_secret'),
        ];
        $response = $client->post('/token', $data);

        $response = json_decode($response->getBody()->getContents());

        ($this->generate_token_callback)($response->access_token);
    }

    public function delete($file_id) 
    {
        $this->ensureTokenAvailable();

        $attempts = 0;

        $response = null;

        do {
            try {
                $client = new Client(
                    [
                        'base_uri' => config('filestorage.host_uri'),
                    ]
                );

                $data['headers'] = [
                    'x-code' => $this->getToken(),
                    'x-client-id' => config('filestorage.client_id'),
                    'Content-Type' => 'application/json',
                ];

                $response = $client->delete('/d/files/' . $file_id, $data);
            } catch (ServerException $e) {
                // Handle exception lain dari API
                $attempts++;
                $this->generateToken();
                continue;
            }

            break;
        } while ($attempts < $this->max_retry);

        if (!$response) {
            throw new ServerFailure('Server error.');
        }

        return json_decode($response->getBody()->getContents());
    }

    public function getFileById($file_id) 
    {
        $this->ensureTokenAvailable();

        $attempts = 0;

        $response = null;

        do {
            try {
                $client = new Client(
                    [
                        'base_uri' => config('filestorage.host_uri'),
                    ]
                );

                $data['headers'] = [
                    'x-code' => $this->getToken(),
                    'x-client-id' => config('filestorage.client_id'),
                    'Content-Type' => 'application/json',
                ];

                $response = $client->get('/d/files/' . $file_id, $data);
            } catch (ServerException $e) {
                // Handle exception lain dari API
                $attempts++;
                $this->generateToken();
                continue;
            }

            break;
        } while ($attempts < $this->max_retry);

        if (!$response) {
            throw new ServerFailure('Server error.');
        }

        return json_decode($response->getBody()->getContents());
    }

    public function setMaxRetry(int $max_retry): void
    {
        $this->max_retry = $max_retry;
    }

    public function getAwsClient() : S3Client 
    {
        $s3 = new S3Client([
            'version' => 'latest',
            'region' => config('filestorage.aws_region'),
            'credentials' => [
                'key'    => config('filestorage.aws_key'),
                'secret' => config('filestorage.aws_secret')
            ]
        ]);	

        return $s3;
    }

    public function awsUpload($request, string $subdirectory = null, string $bucketname = null)
    {
        if($request instanceof UploadedFile) {
            $filename_extension = $request->getClientOriginalName();

            $original_name = pathinfo($filename_extension, PATHINFO_FILENAME);
            $original_name = preg_replace("/[^a-zA-Z0-9]+/", "", $original_name);
            if ($original_name == '') {
                $original_name = 'undefined' . time();
            }

            $extension = $request->extension();
            $filename = $request->hashName();
            $filesize = $request->getSize();
            $mime_type = $request->getClientMimeType();

            $datafile = file_get_contents($request);

        } elseif ($request instanceof File) {
            $original_name = pathinfo($request->path(), PATHINFO_FILENAME);
            $original_name = preg_replace("/[^a-zA-Z0-9]+/", "", $original_name);
            if ($original_name == '') {
                $original_name = 'undefined' . time();
            }

            $extension = pathinfo($request->getRealPath(), PATHINFO_EXTENSION);
            $filename = $request->hashName();
            $filesize = $request->getSize();
            $mime_type = $request->getMimeType();

            $datafile = file_get_contents($request);

        } else {
            throw new InvalidArgument('Unsupported argument type.');
        }

        $response = null;

        try {
            $client = $this->getAwsClient();

            $file_id = '';

            if($subdirectory){
                $file_id .= $subdirectory.'/';
            }

            $file_id .= $filename;

            $result =  $client->putObject([
                'Bucket' => !is_null($bucketname) ? $bucketname : config('filestorage.aws_bucket'),
                'Key'    => $file_id,
                'Body' => $datafile
            ]);

            $data = array(
                'file_id' => $file_id,
                'info' => array(
                    'file_ext' => $extension,
                    'file_id' => $file_id,
                    'file_mimetype' => $mime_type,
                    'file_name' => $original_name,
                    'file_size' => $filesize,
                    'public_link' => $result->get('ObjectURL'),
                    'tag' => $result->get('ETag'),
                    'timestamp' => date('Y-m-d H:m:s')
                    ),
                'message' => 'INSERT '.$file_id,
                'status' => self::STATUS_SUCCESS
            );
            $response = json_decode(json_encode($data), FALSE);

        } catch (S3Exception $e) {
            $data = array(
                'message' => $e->getAwsErrorMessage(),
                'status' => self::STATUS_ERROR
            );

            $response = json_decode(json_encode($data), FALSE);
        }

        if (!$response) {
            throw new ServerFailure('Server error.');
        }

        return $response;

    }

    public function awsDelete(string $aws_file_id, string $bucketname = null)
    {
        $response = null;

        try {
            $client = $this->getAwsClient();

            $result =  $client->deleteObject([
                'Bucket' => !is_null($bucketname) ? $bucketname : config('filestorage.aws_bucket'),
                'Key'    => $aws_file_id
            ]);

            $data = array(
                'message' => 'DELETE '.$aws_file_id,
                'status' => self::STATUS_SUCCESS
            );

            $response = json_decode(json_encode($data), FALSE);
        } catch (S3Exception $e) {
            $data = array(
                'message' => $e->getAwsErrorMessage(),
                'status' => self::STATUS_ERROR
            );

            $response = json_decode(json_encode($data), FALSE);
        }

        if (!$response) {
            throw new ServerFailure('Server error.');
        }

        return $response;

    }

    public function awsGetFileById(string $aws_file_id, string $bucketname = null)
    {
        $response = null;

        try {
            $client = $this->getAwsClient();

            $result =  $client->getObject([
                'Bucket' => !is_null($bucketname) ? $bucketname : config('filestorage.aws_bucket'),
                'Key'    => $aws_file_id
            ]);

            $data = array(
                'data' => base64_encode($result->get('Body')->getContents()),
                'info' => array(
                    'file_ext' => pathinfo($result->get('@metadata')['effectiveUri'], PATHINFO_EXTENSION),
                    'file_mimetype' => $result->get('ContentType'),
                    'file_id' => $aws_file_id,
                    'file_size' => $result->get('ContentLength'),
                    'public_link' => $result->get('@metadata')['effectiveUri'],
                    'tag' => $result->get('ETag'),
                    'timestamp' => date('Y-m-d H:m:s')
                    ),
                'status' => self::STATUS_SUCCESS
            );

            $response = json_decode(json_encode($data), FALSE);
        } catch (S3Exception $e) {
            $data = array(
                'message' => $e->getAwsErrorMessage(),
                'status' => self::STATUS_ERROR
            );

            $response = json_decode(json_encode($data), FALSE);
        }

        if (!$response) {
            throw new ServerFailure('Server error.');
        }

        return $response;
    }

    public function awsDownloadFile(string $aws_file_id, string $bucketname = null, string $saveAspath)
    {
        $response = null;
        try {
            $client = $this->getAwsClient();

            $result = $client->getObject(array(
                'Bucket' => !is_null($bucketname) ? $bucketname : config('filestorage.aws_bucket'),
                'Key'    => $aws_file_id,
                'SaveAs'    => $saveAspath
            ));

            $data = array(
                'message' => 'File success saved to '.$saveAspath,
                'status' => self::STATUS_SUCCESS
            );
            
            $response = json_decode(json_encode($data), FALSE);        

        } catch (S3Exception $e) {
            if(file_exists($saveAspath)){
                unlink($saveAspath);
            }

            $data = array(
                'message' => $e->getAwsErrorMessage(),
                'status' => self::STATUS_ERROR
            );

            $response = json_decode(json_encode($data), FALSE);        
        }

        if (!$response) {
            throw new ServerFailure('Server error.');
        }

        return $response;
    }

    public function awsGetTemporaryPublicLink(string $aws_file_id, DateTime $datetime = null, string $bucketname = null)
    {
        $response = null;

        if(!$datetime){
            $datetime = Carbon::now()->addMinutes(30);
        }

        try {
            $client = $this->getAwsClient();

            $cmd =  $client->getCommand('GetObject',[
                'Bucket' => !is_null($bucketname) ? $bucketname : config('filestorage.aws_bucket'),
                'Key'    => $aws_file_id
            ]);

            $request = $client->createPresignedRequest($cmd, $datetime);

            $data = array(
                'url' => (string) $request->getUri(),
                'expired_at' => $datetime->format('Y-m-d H:i:s'), // Changed to format method
                'status' => self::STATUS_SUCCESS
            );

            $response = json_decode(json_encode($data), FALSE);
        } catch (S3Exception $e) {
            $data = array(
                'message' => $e->getAwsErrorMessage(),
                'status' => self::STATUS_ERROR
            );

            $response = json_decode(json_encode($data), FALSE);
        }

        if (!$response) {
            throw new ServerFailure('Server error.');
        }

        return $response;
    }

    public function getGcsClient(string $projectId = null)
    {
        $keyFilePath = config('filestorage.gcs_key_path');
        if(empty($projectId)){
            $projectId = config('filestorage.gcs_project_id');
        }

        if(empty($keyFilePath) | empty($projectId)){
            throw new InvalidArgument('Credential Not Found');
        }

        $storage = new StorageClient([
            'keyFilePath' => $keyFilePath,
            'projectId' => $projectId
        ]);

        return $storage;
    }

    public function gcsUpload(UploadedFile|File $file, string $subdirectory = null, string $bucketname = null, string $projectId = null)
    {
        if($file instanceof UploadedFile) {
            $filename_extension = $file->getClientOriginalName();

            $original_name = pathinfo($filename_extension, PATHINFO_FILENAME);
            $original_name = preg_replace("/[^a-zA-Z0-9]+/", "", $original_name);
            if ($original_name == '') {
                $original_name = 'undefined' . time();
            }

            $extension = $file->extension();
            $datafile = $file->get();
        } elseif ($file instanceof File) {
            $original_name = pathinfo($file->path(), PATHINFO_FILENAME);
            $original_name = preg_replace("/[^a-zA-Z0-9]+/", "", $original_name);
            if ($original_name == '') {
                $original_name = 'undefined' . time();
            }

            $extension = pathinfo($file->getRealPath(), PATHINFO_EXTENSION);
            $datafile = file_get_contents($file);
        } else {
            throw new InvalidArgument('Unsupported argument type.');
        }

        $response = null;

        try {
            $client = $this->getGcsClient($projectId);

            $file_id = $file->hashName($subdirectory);

            if(empty($bucketname)){
                $bucketname = config('filestorage.gcs_bucket');
            }

            if(empty($bucketname)){
                $bucketname = config('filestorage.gcs_bucket');
            }

            $bucket = $client->bucket($bucketname);

            if(!$bucket->exists()){
                throw new InvalidArgument('Bucket Not Found');
            }

            $result = $bucket->upload($datafile, ['name' => $file_id]);

            $data = array(
                'file_id' => $file_id,
                'info' => array(
                    'file_ext' => $extension,
                    'file_id' => $file_id,
                    'file_mimetype' => $result->info()['contentType'],
                    'bucket' => $result->info()['bucket'],
                    'file_name' => $original_name,
                    'file_size' => $result->info()['size'],
                    'public_link' => $result->info()['mediaLink'],
                    'tag' => $result->info()['etag'],
                    'timestamp' => date('Y-m-d H:m:s')
                    ),
                'message' => 'INSERT '.$file_id,
                'status' => self::STATUS_SUCCESS
            );

            $response = json_decode(json_encode($data), FALSE);
        } catch (GoogleException $th) {
            $data = array(
                'message' => $th->getMessage(),
                'status' => self::STATUS_ERROR
            );

            $response = json_decode(json_encode($data), FALSE);
        }

        if (!$response) {
            throw new ServerFailure('Server error.');
        }

        return $response;
    }
    public function  gcsDelete(string $gcs_file_id, string $bucketname = null, string $projectId = null)
    {
        try {
            $client = $this->getGcsClient($projectId);

            if(empty($bucketname)){
                $bucketname = config('filestorage.gcs_bucket');
            }

            $bucket = $client->bucket($bucketname);

            if(!$bucket->exists()){
                throw new InvalidArgument('Bucket Not Found');
            }

            $result = $bucket->object($gcs_file_id);

            if($result->exists()){

                $result->delete();

                $data = array(
                    'message' => 'DELETE '.$gcs_file_id,
                    'status' => self::STATUS_SUCCESS
                );
            }else{
                $data = array(
                    'message' => 'Object '.$gcs_file_id.' Not Found.',
                    'status' => self::STATUS_ERROR
                ); 
            }

            $response = json_decode(json_encode($data), FALSE);
        } catch (GoogleException $th) {
            $data = array(
                'message' => $th->getMessage(),
                'status' => self::STATUS_ERROR
            );

            $response = json_decode(json_encode($data), FALSE);
        }

        if (!$response) {
            throw new ServerFailure('Server error.');
        }

        return $response;
    }
    public function  gcsGetFileById(string $gcs_file_id, string $bucketname = null, string $projectId = null)
    {
        try {
            $client = $this->getGcsClient($projectId);

            if(empty($bucketname)){
                $bucketname = config('filestorage.gcs_bucket');
            }

            $bucket = $client->bucket($bucketname);

            if(!$bucket->exists()){
                throw new InvalidArgument('Bucket Not Found');
            }

            $result = $bucket->object($gcs_file_id);

            if(!$result->exists()){
                $data = array(
                    'message' => 'Object '.$gcs_file_id.' Not Found.',
                    'status' => self::STATUS_ERROR
                );                 
            }else{
                $data = array(
                    'data' => base64_encode($result->downloadAsString()),
                    'info' => array(
                        'file_ext' => pathinfo($result->info()['name'], PATHINFO_EXTENSION),
                        'file_id' => $gcs_file_id,
                        'file_mimetype' => $result->info()['contentType'],
                        'bucket' => $result->info()['bucket'],
                        'file_size' => $result->info()['size'],
                        'public_link' => $result->info()['mediaLink'],
                        'tag' => $result->info()['etag'],
                        'timestamp' => $result->info()['timeCreated']
                        ),
                    'status' => self::STATUS_SUCCESS
                );
            }

            $response = json_decode(json_encode($data), FALSE);
        } catch (GoogleException $th) {
            $data = array(
                'message' => $th->getMessage(),
                'status' => self::STATUS_ERROR
            );

            $response = json_decode(json_encode($data), FALSE);
        }

        if (!$response) {
            throw new ServerFailure('Server error.');
        }

        return $response;
    }
    public function  gcsDownloadFile(string $gcs_file_id, string $saveAspath, string $bucketname = null, string $projectId = null)
    {
        try {
            $client = $this->getGcsClient($projectId);

            if(empty($bucketname)){
                $bucketname = config('filestorage.gcs_bucket');
            }

            $bucket = $client->bucket($bucketname);

            if(!$bucket->exists()){
                throw new InvalidArgument('Bucket Not Found');
            }

            $result = $bucket->object($gcs_file_id);

            if(!$result->exists()){
                $data = array(
                    'message' => 'Object '.$gcs_file_id.' Not Found.',
                    'status' => self::STATUS_ERROR
                );                 
            }else{
                $result->downloadToFile($saveAspath);

                $data = array(
                    'message' => 'File success saved to '.$saveAspath,
                    'status' => self::STATUS_SUCCESS
                );
            }

            $response = json_decode(json_encode($data), FALSE);
        } catch (GoogleException $th) {
            $data = array(
                'message' => $th->getMessage(),
                'status' => self::STATUS_ERROR
            );

            $response = json_decode(json_encode($data), FALSE);
        }

        if (!$response) {
            throw new ServerFailure('Server error.');
        }

        return $response;
    }
    public function  gcsGetFileByIdAsString(string $gcs_file_id, string $bucketname = null, string $projectId = null)
    {
        try {
            $client = $this->getGcsClient($projectId);

            if(empty($bucketname)){
                $bucketname = config('filestorage.gcs_bucket');
            }

            $bucket = $client->bucket($bucketname);

            if(!$bucket->exists()){
                throw new InvalidArgument('Bucket Not Found');
            }

            $result = $bucket->object($gcs_file_id);

            if(!$result->exists()){
                $data = array(
                    'message' => 'Object '.$gcs_file_id.' Not Found.',
                    'status' => self::STATUS_ERROR
                );                 
            }else{
                $newdata = new stdClass();
                $newdata->status = self::STATUS_SUCCESS;
                $newdata->string_data = $result->downloadAsString();

                return $newdata;
            }

            $response = json_decode(json_encode($data), FALSE);
        } catch (GoogleException $th) {
            $data = array(
                'message' => $th->getMessage(),
                'status' => self::STATUS_ERROR
            );

            $response = json_decode(json_encode($data), FALSE);
        }

        if (!$response) {
            throw new ServerFailure('Server error.');
        }

        return $response;
    }
    public function  gcsGetFileByIdAsStream(string $gcs_file_id, string $bucketname = null, string $projectId = null)
    {
        try {
            $client = $this->getGcsClient($projectId);

            if(empty($bucketname)){
                $bucketname = config('filestorage.gcs_bucket');
            }

            $bucket = $client->bucket($bucketname);

            if(!$bucket->exists()){
                throw new InvalidArgument('Bucket Not Found');
            }

            $result = $bucket->object($gcs_file_id);

            if(!$result->exists()){
                $data = array(
                    'message' => 'Object '.$gcs_file_id.' Not Found.',
                    'status' => self::STATUS_ERROR
                );                 
            }else{
                $newdata = new stdClass();
                $newdata->status = self::STATUS_SUCCESS;
                $newdata->stream_data = $result->downloadAsStream();

                return $newdata;
            }

            $response = json_decode(json_encode($data), FALSE);
        } catch (GoogleException $th) {
            $data = array(
                'message' => $th->getMessage(),
                'status' => self::STATUS_ERROR
            );

            $response = json_decode(json_encode($data), FALSE);
        }

        if (!$response) {
            throw new ServerFailure('Server error.');
        }

        return $response;
    }
    public function  gcsGetTemporaryPublicLink(string $gcs_file_id, DateTime $datetime = null, string $bucketname = null, string $projectId = null)
    {
        $response = null;

        if(!$datetime){
            $datetime = Carbon::now()->addMinutes(30);
        }
        
        try {
            $client = $this->getGcsClient($projectId);

            if(empty($bucketname)){
                $bucketname = config('filestorage.gcs_bucket');
            }

            $bucket = $client->bucket($bucketname);

            if(!$bucket->exists()){
                throw new InvalidArgument('Bucket Not Found');
            }

            $result = $bucket->object($gcs_file_id);

            if(!$result->exists()){
                $data = array(
                    'message' => 'Object '.$gcs_file_id.' Not Found.',
                    'status' => self::STATUS_ERROR
                );                 
            }else{

                $data = array(
                    'url' => (string) $result->signedUrl($datetime),
                    'expired_at' => $datetime->format('Y-m-d H:i:s'), // Changed to format method
                    'status' => self::STATUS_SUCCESS
                );
            }

            $response = json_decode(json_encode($data), FALSE);
        } catch (GoogleException $th) {
            $data = array(
                'message' => $th->getMessage(),
                'status' => self::STATUS_ERROR
            );

            $response = json_decode(json_encode($data), FALSE);
        }

        if (!$response) {
            throw new ServerFailure('Server error.');
        }

        return $response;
    }

}
