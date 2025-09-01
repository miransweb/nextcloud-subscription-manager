<?php
return [
    'routes' => [
        ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
        ['name' => 'api#getStatus', 'url' => '/api/status', 'verb' => 'GET'],
        ['name' => 'api#getWebshopUrl', 'url' => '/api/webshop-url', 'verb' => 'GET'],
    ]
];