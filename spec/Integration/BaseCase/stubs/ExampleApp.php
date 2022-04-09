<?php

namespace Integration\BaseCase\stubs;

class ExampleApp extends \SWEW\Framework\SwewApplication
{
    public array $routers = [
        __DIR__ . '/router/router.php',
    ];
}
