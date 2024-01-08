<?php

declare(strict_types=1);

use Swew\Framework\Base\BaseDTO;
use Swew\Framework\Container\Container;
use Swew\Framework\Env\EnvContainer;
use Swew\Framework\Http\RequestWrapper;
use Swew\Framework\Http\ResponseWrapper;

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
function res(BaseDTO|string|array |null $data = null): ResponseWrapper
{
    $response = ResponseWrapper::getInstance();

    responseState($data);

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

function responseState(mixed &$data = null): mixed
{
    static $storeData = null;

    if (!is_null($data)) {
        $storeData = $data;
    }

    return $storeData;
}

// #region [ helpers ]

if (!function_exists('public_path')) {
    function public_path($path = ''): string
    {
        return env('APP_ROOT') . DIRECTORY_SEPARATOR . env('APP_PUBLIC_DIR') . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : $path);
    }
}

if (!function_exists('cache_path')) {
    function cache_path($path = ''): string
    {
        return env('APP_ROOT') . DIRECTORY_SEPARATOR . env('APP_CACHE_DIR') . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : $path);
    }
}



// #endregion
