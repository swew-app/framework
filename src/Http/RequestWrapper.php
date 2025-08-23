<?php

declare(strict_types=1);

namespace Swew\Framework\Http;

use Exception;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Swew\Framework\Support\Obj;

final class RequestWrapper extends Request
{
    private static ?RequestWrapper $instance = null;

    private array $middlewareNames = [];

    private function __construct(
        string $method = '',
        UriInterface|string $uri = '',
        array $headers = [],
        StreamInterface|string|null $body = null,
        string $version = '1.1',
        array $serverParams = [],
    ) {
        if ($method === '') {
            /** @psalm-suppress RiskyTruthyFalsyComparison */
            if (empty($_SERVER['REQUEST_METHOD'])) {
                throw new Exception('Empty "REQUEST_METHOD"');
            }
            $method = $_SERVER['REQUEST_METHOD'];
        }

        if ($uri === '') {
            /** @psalm-suppress RiskyTruthyFalsyComparison */
            if (empty($_SERVER['REQUEST_URI'])) {
                throw new Exception('Empty "REQUEST_URI"');
            }
            $uri = $_SERVER['REQUEST_URI'];
        }

        parent::__construct($method, $uri, $headers, $body, $version, $serverParams);

        self::$instance = $this;
    }

    public static function new(): self
    {
        self::removeInstance();

        return self::getInstance();
    }

    public static function getInstance(
        string $method = '',
        UriInterface|string $uri = '',
        array $headers = [],
        StreamInterface|string|null $body = null,
        string $version = '1.1',
        array $serverParams = [],
    ): self {
        if (is_null(self::$instance)) {
            return new self(
                $method,
                $uri,
                $headers,
                $body,
                $version,
                $serverParams,
            );
        }

        return self::$instance;
    }

    /**
     * Remove singleton
     */
    public static function removeInstance(): void
    {
        self::$instance = null;
    }

    /**
     * Retrieve an input item from the request.
     */
    public function input(?string $key = null, mixed $default = null): array|object
    {
        $data = $this->getQueryParams();
        $body = $this->getParsedBody();

        if (is_array($body)) {
            $data += $body;
        }

        if ($key !== null && $key !== '' && $default !== null) {
            return $data[$key] ?? $default;
        }

        return $data;
    }

    public function mapTo(object|string $item): string|object
    {
        if (gettype($item) === 'string' && class_exists($item)) {
            $item = new $item();
        }

        $data = $this->getParsedBody();

        if (! is_array($data)) {
            throw new \LogicException('Passed invalid data from request');
        }

        /** @var object $item */
        $data = array_merge(Obj::getPublicVars($item), $data);

        foreach ($data as $key => $value) {
            if (property_exists($item, $key)) {
                $item->{$key} = $value;
            }
        }

        return $item;
    }

    public function isAjax(): bool
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';

        /** @psalm-suppress RedundantCast */
        $contentType = strtolower((string) $contentType);

        return str_contains($contentType, 'json') || str_contains($contentType, 'javascript') || str_contains($contentType, 'xmlhttprequest');
    }

    public function isCLi(): bool
    {
        return PHP_SAPI === 'cli';
    }

    public function setMiddlewareNames(array $middlewareNames): void
    {
        $this->middlewareNames = $middlewareNames;
    }

    public function hasMiddlewareName(string $middlewareName): bool
    {
        return in_array($middlewareName, $this->middlewareNames, strict: true);
    }
}
