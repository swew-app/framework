<?php

declare(strict_types=1);

namespace Swew\Framework\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SplQueue;
use Swew\Framework\Http\ResponseWrapper;

final class MiddlewarePipeline implements MiddlewareInterface, RequestHandlerInterface
{
    private readonly SplQueue $queue;

    private ResponseInterface $response;

    public function __construct(array $middlewares = [])
    {
        $this->queue = new SplQueue();
        $this->response = ResponseWrapper::getInstance();

        foreach ($middlewares as $middleware) {
            $this->pipe($middleware);
        }
    }

    /**
     * PSR-15 middleware invocation.
     *
     * Executes the internal pipeline, passing $handler as the "final
     * handler" in cases when the pipeline exhausts itself.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle($request);
    }

    public function handle(mixed $request): ResponseInterface
    {
        if (!$this->queue->isEmpty()) {
            $middleware = $this->queue->dequeue();
            $this->response = $middleware->process($request, $this);

            return $this->response;
        }

        return $this->response;
    }

    /**
     * Attach middleware to the pipeline.
     */
    public function pipe(MiddlewareInterface $middleware): void
    {
        $this->queue->enqueue($middleware);
    }
}
