<?php

namespace Integration\BaseCase\stubs;

include_once 'DTO/PostDTO.php';

class ExampleApp extends \SWEW\Framework\SwewApplication
{
    public array $routeFiles = [
        __DIR__ . '/router/router.php',
    ];
}
