<?php

declare(strict_types=1);

namespace Swew\Framework\Manager;

use Closure;
use Psr\Http\Server\MiddlewareInterface;
use Swew\Framework\Manager\Lib\CallableHandlerMiddleware;
use Swew\Framework\Manager\Lib\ControllerHandlerMiddleware;

final class AppMiddlewareManager
{
    public function __construct(
        private readonly array $middlewares,
        private readonly array $globalMiddlewares
    ) {
    }

    public function getMiddlewaresForApp(string|Closure $class, string $method = '', array $middlewareNames = []): array
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
     * @param string $alias
     * @return MiddlewareInterface
     */
    private function getMiddleware(string $alias): MiddlewareInterface
    {
        $middlewareClassName = $this->middlewares[$alias];

        if (!class_exists($middlewareClassName)) {
            throw new \LogicException("Can't find middleware with name '$alias' ");
        }

        /** @var MiddlewareInterface $middlewareClass */
        $middlewareClass = new $middlewareClassName();

        return $middlewareClass;
    }

    private function makeControllerMiddleware(string|Closure $class, string $method): MiddlewareInterface
    {
        if (is_callable($class)) {
            /** @var Closure $class */
            return new CallableHandlerMiddleware($class);
        }
        return new ControllerHandlerMiddleware($class, $method);
    }
}
