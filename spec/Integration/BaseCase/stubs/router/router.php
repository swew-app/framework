<?php

use Integration\BaseCase\stubs\controllers\ExampleController;

return [
    [
        'name' => 'MainExamplePage',
        'path' => '/',
        'controller' => ExampleController::class,
    ],

    [
        'name' => 'AboutExamplePage',
        'path' => '/about',
        'controller' => [ExampleController::class, 'about'],
    ],


    [
        'name' => 'BlogExamplePage',
        'path' => '/blog/{id}',
        'controller' => [ExampleController::class, 'blog'],
        'method' => 'GET',
    ],

    [
        'name' => 'BlogAddPage',
        'path' => '/blog/{postId}',
        'controller' => [ExampleController::class, 'storePost'],
        'method' => 'POST'
    ],

    [
        'name' => 'AdminPage',
        'path' => '/admin',
        'controller' => ExampleController::class,
        'method' => 'GET',
        'middlewares' => ['cors'],
    ]
];
