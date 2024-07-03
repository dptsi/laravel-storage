<?php

return [
    'host_uri'                  => env('FILE_STORAGE_HOST_URI'),
    'authorization_server_uri'  => env('FILE_STORAGE_AUTHORIZATION_SERVER_URI'),
    'client_id'                 => env('FILE_STORAGE_CLIENT_ID'),
    'client_secret'             => env('FILE_STORAGE_CLIENT_SECRET'),
    //config aws
    'aws_key'                   => env('AWS_ACCESS_KEY_ID'),
    'aws_secret'                => env('AWS_SECRET_ACCESS_KEY'),
    'aws_region'                => env('AWS_DEFAULT_REGION'),
    'aws_bucket'                => env('AWS_BUCKET'),
    //config gcs
    'gcs_key_path'              => env('GOOGLE_KEY_PATH'),
    'gcs_project_id'            => env('GOOGLE_PROJECT_ID'),
    'gcs_bucket'                => env('GOOGLE_BUCKET'),
];