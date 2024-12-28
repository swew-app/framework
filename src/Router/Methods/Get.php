<?php

declare(strict_types=1);

namespace Swew\Framework\Router\Methods;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Get extends MethodContract
{
    public function __construct(string $path, string $name = '', array $middlewares = [])
    {
        $this->path = $path;
        $this->name = $name;
        $this->middlewares = $middlewares;
    }

    public function getMethod(): string
    {
        return 'GET';
    }
}
