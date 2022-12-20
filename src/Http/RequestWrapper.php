<?php

declare(strict_types=1);

namespace Swew\Framework\Http;

use Exception;
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
        if (!is_null(self::$instance)) {
            return self::$instance;
        }

        if ($method === '') {
            if (empty($_SERVER['REQUEST_METHOD'])) {
                throw new Exception('Empty "REQUEST_METHOD"');
            }
            $method = $_SERVER['REQUEST_METHOD'];
        }

        if ($uri === '') {
            if (empty($_SERVER['REQUEST_URI'])) {
                throw new Exception('Empty "REQUEST_URI"');
            }
            $uri = $_SERVER['REQUEST_URI'];
        }

        parent::__construct($method, $uri, $headers, $body, $version, $serverParams);

        self::$instance = $this;
    }

    public static function getInstance(): self
    {
        if (is_null(self::$instance)) {
            return new self();
        }

        return self::$instance;
    }

    /**
     * Remove singleton
     *
     * @return void
     */
    public static function removeInstance(): void
    {
        self::$instance = null;
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

    public function isAjax(): bool
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';

        $contentType = strtolower($contentType);

        return str_contains($contentType, 'json')
            || str_contains($contentType, 'javascript')
            || str_contains($contentType, 'xmlhttprequest');
    }
}
