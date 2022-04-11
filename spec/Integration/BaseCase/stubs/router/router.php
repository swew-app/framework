<?php

use Integration\BaseCase\stubs\controllers\ExampleController;

return [
    '/' => [
        'name' => 'MainExamplePage',
        'controller' => ExampleController::class,
    ],

    '/about' => [
        'name' => 'AboutExamplePage',
        'controller' => [ExampleController::class, 'about'],
    ],


    '/blog/{id}' => [
        'name' => 'BlogExamplePage',
        'controller' => [ExampleController::class, 'blog'],
        'method' => 'GET',
    ],

    '/blog/{postId}' => [
        'name' => 'BlogAddPage',
        'controller' => [ExampleController::class, 'storePost'],
        'method' => 'POST'
    ],
];
