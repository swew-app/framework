<?php

declare(strict_types=1);

namespace Swew\Framework\Http;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

final class RequestWrapper extends Request
{
    private static ?RequestWrapper $instance = null;

    public function __construct(
        string                      $method = '',
        UriInterface|string         $uri = '',
        array                       $headers = [],
        StreamInterface|string|null $body = null,
        string                      $version = '1.1',
        array                       $serverParams = []
    ) {
        if (!is_null(RequestWrapper::$instance)) {
            return RequestWrapper::$instance;
        }

        if ($method === '') {
            $method = $_SERVER['REQUEST_METHOD'];
        }

        if ($uri === '') {
            $uri = $_SERVER['REQUEST_URI'];
        }

        parent::__construct($method, $uri, $headers, $body, $version, $serverParams);

        RequestWrapper::$instance = $this;
    }

    /**
     * Retrieve an input item from the request.
     *
     * @param string|null $key
     * @param mixed|null $default
     * @return mixed
     */
    public function input(string $key = null, mixed $default = null): mixed
    {
        $data = $this->getQueryParams();
        $body = $this->getParsedBody();

        if (is_array($body)) {
            $data = $data + $body;
        }

        if ($key) {
            return $data[$key] ?? $default;
        }

        return $data;
    }
}
