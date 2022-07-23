<?php

namespace Swew\Framework\Manager\Lib;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Swew\Framework\Http\Response;

final class ControllerHandlerMiddleware implements \Psr\Http\Server\MiddlewareInterface
{
    public function __construct(
        private readonly string $class,
        private readonly string $method
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $classInstance = new $this->class();

        $method = $this->method;

        $result = $classInstance->$method();

        if ($result instanceof Response) {
            return $result;
        }

//        if (is_string($result) || $result instanceof BaseDTO) {
        if (is_string($result)) {
            return res($result);
        }

        return res();
    }
}
