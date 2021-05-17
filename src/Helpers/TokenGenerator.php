<?php

namespace Dptsi\FileStorage\Helpers;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class TokenGenerator 
{
    protected static function generateToken()
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

        Cache::put('access_token', $response->access_token, $seconds = 3550);
    }

    public static function checkToken()
    {
        if(Cache::has('access_token')){
            return;
        } else {
            self::generateToken();
            sleep(2);
        }
    }
}