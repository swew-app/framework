<?php

namespace Swew\Testing\Integration\stubs\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class CorsMiddleware implements MiddlewareInterface
{
    public function handle(): bool
    {
        return true;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $headers = [
            'Access-Control-Allow-Origin' => $_SERVER['HTTP_ORIGIN'] ?? '*',
            'Access-Control-Allow-Methods' => 'POST, GET, OPTIONS, PUT, DELETE',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age' => '86400',
        ];

        if ($request->getMethod() === 'OPTIONS') {
            return res('{"method":"OPTIONS"}');
        }

        $response = $handler->handle($request);

        foreach ($headers as $key => $value) {
            $response = $response->withHeader($key, $value);
        }

        return $response;
    }
}
