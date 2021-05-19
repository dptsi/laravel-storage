<?php

namespace Dptsi\FileStorage\Core;

use Dptsi\FileStorage\Helpers\TokenGenerator;
use GuzzleHttp\Client;
use Illuminate\Http\UploadedFile;

class FileStorageManager
{
    public function upload(UploadedFile $request)
    {
        $filename_extension = $request->getClientOriginalName();

        $filename = pathinfo($filename_extension, PATHINFO_FILENAME);
        $filename = preg_replace("/[^a-zA-Z0-9]+/", "", $filename);
        if ($filename == '') {
            $filename = 'undefined'.time();
        }

        $extension = pathinfo($filename_extension, PATHINFO_EXTENSION);

        $b64 = base64_encode(file_get_contents($request));

        TokenGenerator::checkToken();

        $client = new Client([
            'base_uri'  => config('filestorage.base_uri'),
        ]);

        $data['headers'] = [
            'x-code'        => TokenGenerator::getTokenCallback(),
            'x-client-id'   => config('filestorage.client_id'),
            'Content-Type'  => 'application/json',
        ];

        $data['body'] = json_encode([
            'file_name'         => $filename,
            'file_ext'          => $extension,
            'mime_type'         => $request->getMimeType(),
            'binary_data_b64'   => $b64,
        ]);

        $response = $client->post('/d/files', $data);

        return json_decode($response->getBody()->getContents());
    }

    public function delete($file_id)
    {
        TokenGenerator::checkToken();

        $client = new Client([
            'base_uri'  => config('filestorage.base_uri'),
        ]);

        $data['headers'] = [
            'x-code'        => TokenGenerator::getTokenCallback(),
            'x-client-id'   => config('filestorage.client_id'),
            'Content-Type'  => 'application/json',
        ];

        $response = $client->delete('/d/files/' . $file_id, $data);

        return json_decode($response->getBody()->getContents());
    }
}