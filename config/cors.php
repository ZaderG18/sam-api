<?php

// config/cors.php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],

    // IMPORTANTE: O endereço exato do seu Front-end
    'allowed_origins' => ['http://localhost:3000'], 

    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,

    // IMPORTANTE: Permite cookies/sessão
    'supports_credentials' => true, 
];