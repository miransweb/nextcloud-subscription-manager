<?php
return [
    'routes' => [
        ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
        ['name' => 'api#getStatus', 'url' => '/api/status', 'verb' => 'GET'],
        ['name' => 'api#getWebshopUrl', 'url' => '/api/webshop-url', 'verb' => 'GET'],
        ['name' => 'admin#saveSettings', 'url' => '/admin/settings', 'verb' => 'POST'],
        ['name' => 'admin#testConnection', 'url' => '/admin/test-connection', 'verb' => 'GET'],
    ]
];