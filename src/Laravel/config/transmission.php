<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Transmission-RPC Config
    |--------------------------------------------------------------------------
    |
    | Transmission-RPC Hostname and Port.
    |
    */

    'hostname' => env('TRANSMISSION_HOSTNAME', '127.0.0.1'),
    'port'     => env('TRANSMISSION_PORT', 9091),

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