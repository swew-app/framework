<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Swew\Framework\Http\Request;
use Swew\Framework\Http\Response;
use Swew\Framework\Middleware\MiddlewarePipeline;

it('Pipe process handler', function (): void {
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

it('Pipe process handler with cancel', function (): void {
    class CancelMiddlewareOne implements MiddlewareInterface
    {
        public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
        {
            $resp = $handler->handle($request);
            $resp = $resp->withHeader('X-Name-1', '101');
            return $resp;
        }
    }

    class AuthMiddlewareOne implements MiddlewareInterface
    {
        public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
        {
            // Cancel Middleware - the next ones shouldn't work out.
            //You can check the statuses
            $resp = new Response();
            $resp->withStatus(401);

            return $resp;
        }
    }

    class CancelMiddlewareTwo implements MiddlewareInterface
    {
        public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
        {
            $resp = $handler->handle($request);
            $resp = $resp->withHeader('X-Name-2', '102');
            return $resp;
        }
    }

    class CancelControllerHandler implements MiddlewareInterface
    {
        public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
        {
            return new Response(301);
        }
    }

    $pipelines = new MiddlewarePipeline();

    $pipelines->pipe(new CancelMiddlewareOne());
    $pipelines->pipe(new AuthMiddlewareOne());
    $pipelines->pipe(new CancelMiddlewareTwo());
    $pipelines->pipe(new CancelControllerHandler()); // Call Controller in process

    $req = new Request('GET', '/');

    $response = $pipelines->handle($req);

    expect($response->getHeader('X-Name-1'))->toBe(['101']);
    // Didn't get a header from CancelMiddlewareTwo
    expect($response->getHeader('X-Name-2'))->not->toBe(['102']);
    // Status from AuthMiddlewareOne
    expect($response->getStatusCode())->toBe(401);
});
