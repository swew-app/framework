<?php

use Swew\Testing\Integration\stubs\Controllers\AdminController;
use Swew\Testing\Integration\stubs\Controllers\ExampleController;
use Swew\Testing\Integration\stubs\Controllers\ManagerController;

return [
    [
        'name' => 'Main',
        'path' => '/',
        'controller' => [ExampleController::class, 'getIndex'],
    ],
    [
        'name' => 'MainPost',
        'path' => '/post',
        'controller' => [ExampleController::class, 'getPost'],
    ],
    [
        'name' => 'Admin',
        'path' => '/admin',
        'controller' => [AdminController::class, 'getIndex'],
//        'children' => [
//            'name' => 'Manager',
//            'path' => '/manager',
//            'controller' => [ManagerController::class, 'dashboard'],
//        ]
    ],
    [
        'name' => 'BadTestPage',
        'path' => '/some-page',
        'controller' => [AdminController::class, 'getSomePage'],
        'middlewares' => ['break'],
    ]
];
