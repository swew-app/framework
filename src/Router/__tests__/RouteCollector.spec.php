<?php

declare(strict_types=1);

include_once 'stub/CollectControllerStub.php';

use Swew\Framework\Router\Router;
use Router\stub\CollectControllerStub;

it('Route collector', function () {
    $router = new Router([
        [
            'collector' => CollectControllerStub::class,
            'middlewares' => ['middleware_1'],
        ]
    ]);


    expect($router->getRoutes())->toBe([
        [
            'collector' => CollectControllerStub::class,
            'middlewares' => ['middleware_1', 'middleware_2'],
            'path' => '/main',
            'controller' => ['Router\\stub\\CollectControllerStub', 'getMainPage'],
            'method' => 'GET',
            'name' => 'Main',
        ],
        [
            'collector' => CollectControllerStub::class,
            'middlewares' => ['middleware_1'],
            'path' => '/about',
            'controller' => ['Router\\stub\\CollectControllerStub', 'getAboutPage'],
            'method' => 'GET',
            'name' => 'getAboutPage',
        ]
    ]);
});

it('Validate Collector', function () {
    $router = new Router([
        [
            'collector' => CollectControllerStub::class,
            'middlewares' => ['middleware_1'],
        ]
    ]);

    expect($router->validate())->toBe(true);
});
