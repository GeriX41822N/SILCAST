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

    'allowed_methods' => ['POST', 'GET', 'OPTIONS', 'PUT', 'PATCH', 'DELETE'],

    'allowed_origins' => [
        'http://localhost:4200',        // Para tu entorno de desarrollo Angular
        'https://silcast.mx',           // ¡IMPORTANTE! La URL de tu sitio web en producción
        
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'], // Cambiado a '*' para permitir todos los headers necesarios

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];