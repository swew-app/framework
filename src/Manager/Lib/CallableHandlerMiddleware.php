<?php

declare(strict_types=1);

namespace Swew\Framework\Manager\Lib;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Swew\Framework\Base\BaseDTO;
use Swew\Framework\Http\Response;

readonly class CallableHandlerMiddleware implements MiddlewareInterface
{
    public function __construct(
        private Closure $callback,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $cb = $this->callback;
        $result = $cb();

        if ($result instanceof Response) {
            return $result;
        }

        if (is_string($result) || $result instanceof BaseDTO) {
            return res($result);
        }

        return res();
    }
}
