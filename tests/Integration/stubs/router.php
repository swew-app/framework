<?php

use Swew\Testing\Integration\stubs\Controllers\AdminController;
use Swew\Testing\Integration\stubs\Controllers\ExampleController;
use Swew\Testing\Integration\stubs\Controllers\ManagerController;

return [
    [
        'name' => 'Main',
        'path' => '/',
        'controller' => ExampleController::class,
    ],
//    [
//        'name' => 'Admin',
//        'path' => '/admin',
//        'controller' => AdminController::class,
//        'children' => [
//            'name' => 'Manager',
//            'path' => '/manager',
//            'controller' => [ManagerController::class, 'dashboard'],
//        ]
//    ]
];
