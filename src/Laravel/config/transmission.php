<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Transmission-RPC Config
    |--------------------------------------------------------------------------
    |
    | Transmission-RPC Host and Port.
    |
    */

    'enableTLS' => env('TRANSMISSION_ENABLE_TLS', false),
    'host'      => env('TRANSMISSION_HOST', '127.0.0.1'),
    'port'      => env('TRANSMISSION_PORT', 9091),

    /*
    |--------------------------------------------------------------------------
    | Transmission-RPC Authentication
    |--------------------------------------------------------------------------
    |
    | If authentication is enabled, provide your username and password.
    |
    */

    'username' => env('TRANSMISSION_USERNAME', ''),
    'password' => env('TRANSMISSION_PASSWORD', ''),

];
