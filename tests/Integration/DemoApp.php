<?php

declare(strict_types=1);

namespace Swew\Testing\Integration;

use Swew\Framework\SwewApp;
use Swew\Testing\Integration\stubs\Middlewares\BreakMiddleware;
use Swew\Testing\Integration\stubs\Middlewares\CorsMiddleware;

class DemoApp extends SwewApp
{
    protected array $routeFiles = [
        __DIR__ . '/stubs/router.php',
    ];

    protected array $middlewares = [
        'cors' => CorsMiddleware::class,
        'break' => BreakMiddleware::class,
    ];

    protected array $globalMiddlewares = [
        'cors',
    ];
}
