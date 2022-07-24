<?php

declare(strict_types=1);

namespace Swew\Testing\Integration;

use Swew\Framework\SwewApp;

class DemoApp extends SwewApp
{
    protected array $routeFiles = [
        __DIR__ . '/stubs/router.php',
    ];
}
