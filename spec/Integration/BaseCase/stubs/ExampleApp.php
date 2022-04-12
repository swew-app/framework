<?php

namespace Integration\BaseCase\stubs;

use Integration\BaseCase\stubs\middlewares\CorsMiddleware;

include_once 'DTO/PostDTO.php';

class ExampleApp extends \SWEW\Framework\SwewApplication
{
    public array $routeFiles = [
        __DIR__ . '/router/router.php',
    ];

    public array $middlewares = [
        'cors' => CorsMiddleware::class
    ];
}
