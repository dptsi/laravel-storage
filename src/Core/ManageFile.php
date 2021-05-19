<?php

namespace Dptsi\FileStorage\Core;

use Dptsi\FileStorage\Helpers\TokenGenerator;
use GuzzleHttp\Client;

class FileStorage 
{

    public function upload(String $file_name, String $file_ext, String $mime_type, String $base64_encoded_data)
    {
        $generator = new TokenGenerator();

        $generator->checkToken();

        $client = new Client([
            'base_uri'  => config('filestorage.base_uri'),
        ]);
        $data['headers'] = [
            'x-code'        => $generator->getTokenCallback(),
            'x-client-id'   => config('filestorage.client_id'),
            'Content-Type'  => 'application/json',
        ];
        $data['body'] = json_encode([
            'file_name'         => $file_name,
            'file_ext'          => $file_ext,
            'mime_type'         => $mime_type,
            'binary_data_b64'   => $base64_encoded_data,
        ]);
        $response = $client->post('/d/files', $data);

        return json_decode($response->getBody()->getContents());
    }

    public function delete($file_id)
    {
        $generator = new TokenGenerator();

        $generator->checkToken();

        $client = new Client([
            'base_uri'  => config('filestorage.base_uri'),
        ]);
        $data['headers'] = [
            'x-code'        => $generator->getTokenCallback(),
            'x-client-id'   => config('filestorage.client_id'),
            'Content-Type'  => 'application/json',
        ];
        $response = $client->delete('/d/files/' . $file_id, $data);

        return json_decode($response->getBody()->getContents());
    }
}