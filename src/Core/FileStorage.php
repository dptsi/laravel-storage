<?php

namespace Dptsi\FileStorage\Core;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Redis;

class FileStorage 
{

    public static function upload($file_name, $file_ext, $mime_type, $base64_encoded_data)
    {
        $client = new Client([
            'base_uri'  => config('filestorage.base_uri'),
        ]);
        $data['headers'] = [
            'x-code'        => Redis::get('access_token'),
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

    public static function delete($file_id)
    {
        $client = new Client([
            'base_uri'  => config('filestorage.base_uri'),
        ]);
        $data['headers'] = [
            'x-code'        => Redis::get('access_token'),
            'x-client-id'   => config('filestorage.client_id'),
            'Content-Type'  => 'application/json',
        ];
        $response = $client->delete('/d/files/' . $file_id, $data);

        return json_decode($response->getBody()->getContents());
    }

    public static function generateToken()
    {
        $client = new Client([
            'base_uri'      => config('filestorage.myits_uri'),
        ]);
        $data['headers'] = [
            'Content-Type'  => 'application/x-www-form-urlencoded'
        ];
        $data['form_params'] = [
            'grant_type'    => 'client_credentials',
            'client_id'     => config('filestorage.client_id'),
            'client_secret' => config('filestorage.client_secret')
        ];
        $response = $client->post('/token', $data);

        $response = json_decode($response->getBody()->getContents());

        Redis::set('access_token', $response->access_token, 'EX', 3600);
    }
}