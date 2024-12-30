<?php

declare(strict_types=1);

namespace Swew\Framework\Router\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Route
{
    public function __construct(
        private readonly string $path,
        private readonly string $name = '',
        private readonly array $methods = [],
        private readonly array $middlewares = []
    ) {
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    public function getMethod(): string
    {
        return implode('|', $this->methods);
    }
}
