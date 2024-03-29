<?php

namespace Swew\Framework\Manager\Lib;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Swew\Framework\Base\BaseDTO;
use Swew\Framework\Http\Response;

final class ControllerHandlerMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly string $class,
        private readonly string $method,
        private readonly ?\Closure $hook = null
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (is_callable($this->hook)) {
            $cb = $this->hook;
            $cb();
        }

        $classInstance = new $this->class();

        $method = $this->method;

        $result = $classInstance->$method();

        if ($result instanceof Response) {
            return $result;
        }

        if (is_string($result) || $result instanceof BaseDTO) {
            return res($result);
        }

        return res();
    }
}
