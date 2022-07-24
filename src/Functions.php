<?php

declare(strict_types=1);

use Swew\Framework\Container\Container;
use Swew\Framework\Env\EnvContainer;
use Swew\Framework\Http\RequestWrapper;
use Swew\Framework\Http\ResponseWrapper;

function req(): RequestWrapper
{
    return new RequestWrapper();
}

function res(?string $data = null): ResponseWrapper
{
    $response = new ResponseWrapper();

    if (is_string($data)) {
        $response->getBody()->write($data);
    }

    return $response;
}

function env(string $key = '', mixed $default = null): mixed
{
    $env = new EnvContainer();

    if ($key === '') {
        return $env;
    }

    return $env->get($key, $default);
}

function container(string $id = ''): mixed
{
    $container = new Container();

    if ($id === '') {
        return $container;
    }

    return $container->get($id);
}
