<?php

namespace Dptsi\FileStorage\Core;

use Closure;
use Dptsi\FileStorage\Exception\ServerFailure;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Http\UploadedFile;

class FileStorageManager
{
    private Closure $generate_token_callback;
    private Closure $request_token_callback;
    private Closure $check_token_callback;
    private int $max_retry = 3;

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

    public function upload(UploadedFile $request)
    {
        $filename_extension = $request->getClientOriginalName();

        $filename = pathinfo($filename_extension, PATHINFO_FILENAME);
        $filename = preg_replace("/[^a-zA-Z0-9]+/", "", $filename);
        if ($filename == '') {
            $filename = 'undefined' . time();
        }

        $extension = pathinfo($filename_extension, PATHINFO_EXTENSION);

        $b64 = base64_encode(file_get_contents($request));

        $this->ensureTokenAvailable();

        $attempts = 0;

        $response = null;

        do {
            try {
                $client = new Client(
                    [
                        'base_uri' => config('filestorage.base_uri'),
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
            throw new ServerFailure();
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
                'base_uri' => config('filestorage.myits_uri'),
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

    public function delete(
        $file_id
    ) {
        $this->ensureTokenAvailable();

        $attempts = 0;

        $response = null;

        do {
            try {
                $client = new Client(
                    [
                        'base_uri' => config('filestorage.base_uri'),
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
            throw new ServerFailure();
        }

        return json_decode($response->getBody()->getContents());
    }

    public function setMaxRetry(int $max_retry): void
    {
        $this->max_retry = $max_retry;
    }
}