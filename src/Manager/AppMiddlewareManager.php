<?php

declare(strict_types=1);

namespace Swew\Framework\Manager;

use Exception;
use Psr\Http\Server\MiddlewareInterface;
use Swew\Framework\Manager\Lib\ControllerHandlerMiddleware;

final class AppMiddlewareManager
{
    public function __construct(
        private readonly array $middlewares,
        private readonly array $globalMiddlewares
    ) {
    }

    public function getAppMiddlewares(string $class, string $method, array $middlewareNames): array
    {
        $middlewaresList = $this->getMiddlewares($middlewareNames);

        $middlewaresList[] = $this->makeControllerMiddleware($class, $method);

        return $middlewaresList;
    }

    private function getMiddlewares(array $aliases): array
    {
        $middlewareInstances = [];

        foreach ($this->globalMiddlewares as $middlewareAlias) {
            $middlewareInstances[] = $this->getMiddleware($middlewareAlias);
        }

        foreach ($aliases as $alias) {
            $middlewareInstances[] = $this->getMiddleware($alias);
        }

        return $middlewareInstances;
    }

    /**
     * @throws Exception
     */
    private function getMiddleware(string $alias): MiddlewareInterface
    {
        $middlewareClassName = $this->middlewares[$alias];

        if (!class_exists($middlewareClassName)) {
            throw new Exception("Can't find middleware with name '$alias' ");
        }

        /** @var MiddlewareInterface $middlewareClass */
        $middlewareClass = new $middlewareClassName();

        return $middlewareClass;
    }

    private function makeControllerMiddleware(string $class, string $method): MiddlewareInterface
    {
        return new ControllerHandlerMiddleware($class, $method);
    }
}
