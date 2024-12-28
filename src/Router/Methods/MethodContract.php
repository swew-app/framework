<?php

declare(strict_types=1);

namespace Swew\Framework\Router\Methods;

abstract class MethodContract
{
    abstract public function getMethod(): string;

    protected string $path;

    protected string $name;

    protected array $middlewares;

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
}
