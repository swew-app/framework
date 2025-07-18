<?php

declare(strict_types=1);

use Swew\Framework\Base\BaseDTO;
use Swew\Framework\Container\Container;
use Swew\Framework\Env\EnvContainer;
use Swew\Framework\Http\RequestWrapper;
use Swew\Framework\Http\ResponseWrapper;
use Swew\Framework\Router\RouteHelper;

/**
 * A helper to create a request
 */
function req(): RequestWrapper
{
    return RequestWrapper::getInstance();
}

/**
 * A helper to create a response
 *
 */
function res(BaseDTO|string|array|null $data = null): ResponseWrapper
{
    $response = ResponseWrapper::getInstance();

    if (!is_null($data)) {
        $response->setStoredData($data);
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
    static $container = new Container();

    if ($id === '') {
        return $container;
    }

    return $container->get($id);
}

function route(
    string $path,
    string|null $name = null,
    string|array|null $controller = null,
    string|null $collector = null,
): RouteHelper {
    $route = new RouteHelper();
    $route->path($path);

    if ($name !== null) {
        $route->name($name);
    }

    if ($controller !== null) {
        $route->controller($controller);
    }

    if ($collector !== null) {
        $route->collector($collector);
    }

    return $route;
}

function url(string $routeName, array $params = []): string
{
    return env('$router')->url($routeName, $params);

}

// #region [ helpers ]

function public_path($path = ''): string
{
    return env('APP_ROOT') . DIRECTORY_SEPARATOR . env('APP_PUBLIC_DIR') . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : $path);
}

function cache_path($path = ''): string
{
    return env('APP_ROOT') . DIRECTORY_SEPARATOR . env('APP_CACHE_DIR') . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : $path);
}



// #endregion
