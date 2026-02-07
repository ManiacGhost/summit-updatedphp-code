<?php 


return [
    'paths' => ['api/*', 'admin/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:5173', 'http://127.0.0.1:5173', 'http://localhost:3000', 'http://127.0.0.1:3000'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'supports_credentials' => true,

];
