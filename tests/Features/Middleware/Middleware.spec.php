<?php

declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Swew\Framework\Http\Request;
use Swew\Framework\Http\Response;
use Swew\Framework\Middleware\MiddlewarePipeline;

it('Pipe process handler', function () {
    class MiddlewareOne implements MiddlewareInterface
    {
        public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
        {
            $resp = $handler->handle($request);
            $resp = $resp->withHeader('X-Name-1', '101');
            return $resp;
        }
    }

    class MiddlewareTwo implements MiddlewareInterface
    {
        public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
        {
            $resp = $handler->handle($request);
            $resp = $resp->withHeader('X-Name-2', '102');
            return $resp;
        }
    }

    class ControllerHandler implements MiddlewareInterface
    {
        public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
        {
            return new Response(301);
        }
    }

    $pipelines = new MiddlewarePipeline();

    $pipelines->pipe(new MiddlewareOne());
    $pipelines->pipe(new MiddlewareTwo());
    $pipelines->pipe(new ControllerHandler()); // Call Controller in process

    $req = new Request('GET', '/');

    $response = $pipelines->handle($req);

    expect($response->getHeader('X-Name-1'))->toBe(['101']);
    expect($response->getHeader('X-Name-2'))->toBe(['102']);
    expect($response->getStatusCode())->toBe(301);
});
