<?php

declare(strict_types=1);

use Swew\Framework\Base\BaseDTO;
use Swew\Framework\Container\Container;
use Swew\Framework\Env\EnvContainer;
use Swew\Framework\Http\RequestWrapper;
use Swew\Framework\Http\ResponseWrapper;

function req(): RequestWrapper
{
    return RequestWrapper::getInstance();
}

function res(BaseDTO|string|array|null $data = null): ResponseWrapper
{
    $response = ResponseWrapper::getInstance();

    if ($data instanceof BaseDTO) {
        $data = $data->getData();
    }

    if (is_array($data)) {
        $data = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);

        if (req()->isAjax()) {
            $response->withHeader('Content-Type', 'application/json');
        }
    }

    if (is_string($data)) {
        $response->getBody()->write($data);
    }

    return $response;
}

function env(string $key = '', mixed $default = null): mixed
{
    $env = EnvContainer::getInstance();

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
