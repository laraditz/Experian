<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    'vendor' => env('EXPERIAN_VENDOR'),
    'username' => env('EXPERIAN_USERNAME'),
    'password' => env('EXPERIAN_PASSWORD'),
    'base_url' => 'https://b2b.experian.com.my/index.php',
    'sandbox' => [
        'mode' => env('EXPERIAN_SANDBOX_MODE', false),
        'base_url' => 'https://b2buat.experian.com.my/index.php',
    ],
    'log_request' => env('EXPERIAN_LOG_REQUEST', false),
    'unallowed_list' => env('EXPERIAN_UNALLOWED_LIST'),
];
