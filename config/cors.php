<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['POST', 'GET', 'OPTIONS', 'PUT', 'PATCH', 'DELETE'], // Métodos explícitos

    'allowed_origins' => [
        '*',
        'http://localhost:4200', // Asegúrate de que tu frontend esté en este puerto
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Authorization', 'Content-Type'], // ¡Modifica esta línea!

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];