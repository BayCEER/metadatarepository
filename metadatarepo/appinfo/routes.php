<?php

namespace OCA\metadatarepo\AppInfo;

$application = new Application();
$application->registerRoutes($this, [
    'routes' => [
        [
            // The handler is the PageController's index method
            'name' => 'page#index',
            // The route
            'url' => '/',
            // Only accessible with GET requests
            'verb' => 'GET'
        ],
        ['name' => 'page#show', 'url'=>'page/{id}', 'verb' =>'GET'],
        ['name' => 'page#thumbnail', 'url'=>'thumbnail/{id}', 'verb' =>'GET'],
        ['name' => 'page#image', 'url'=>'image/{id}', 'verb' =>'GET'],
        ['name' => 'setting#admin', 'url' => '/admin', 'verb' => 'POST'],
        ['name' => 'file#fill', 'url' => '/fill', 'verb' => 'POST'],
    ]
]);
