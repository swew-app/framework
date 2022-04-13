<?php

namespace Integration\BaseCase\stubs\middlewares;

use SWEW\Framework\Base\BaseMiddleware;

final class CorsMiddleware extends BaseMiddleware
{
    public function handle(): bool
    {
        $headers = [
            'Access-Control-Allow-Origin' => $_SERVER['HTTP_ORIGIN'] ?? '*',
            'Access-Control-Allow-Methods' => 'POST, GET, OPTIONS, PUT, DELETE',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age' => '86400',
        ];

        if ($this->app->req->isMethod('OPTIONS')) {
            $this->app->res
                ->setContent('{"method":"OPTIONS"}')
                ->setStatusCode(200);
        }

        foreach ($headers as $key => $value) {
            $this->app->res->headers->set($key, $value);
        }

        return true;
    }

    public function beforeResponse(): void
    {
        $this->app->res->headers->set('x-test-after', 'true');
    }
}
