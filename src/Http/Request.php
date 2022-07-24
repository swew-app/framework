<?php

declare(strict_types=1);

namespace Swew\Framework\Http;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Swew\Framework\Http\Partials\MessageMethods;
use Swew\Framework\Http\Partials\Stream;
use Swew\Framework\Http\Partials\Uri;

class Request extends MessageMethods implements ServerRequestInterface
{
    private UriInterface $uri;
    private string $method = '';
    private array $serverParams = [];
    private string $requestTarget = '/';
    private array $cookieParams = [];
    private array $queryParams = [];
    private array $uploadedFiles = [];
    private array|object|null $parsedBody = null;
    private array $attributes = [];

    /**
     * @param string $method HTTP method
     * @param string|UriInterface $uri URI
     * @param array $headers Request headers
     * @param string|StreamInterface|null $body Request body
     * @param string $version Protocol version
     * @param array $serverParams Typically the $_SERVER superglobal
     */
    public function __construct(
        string                      $method,
        string|UriInterface         $uri,
        array                       $headers = [],
        string|StreamInterface|null $body = null,
        string                      $version = '1.1',
        array                       $serverParams = []
    ) {
        $this->serverParams = $serverParams;

        if (!($uri instanceof UriInterface)) {
            $uri = new Uri($uri);
        }

        $this->method = $method;
        $this->uri = $uri;

        foreach ($headers as $name => $text) {
            $this->withHeader($name, $headers);
        }

        $this->protocol = $version;

        if (!$this->hasHeader('Host')) {
            $this->updateHostFromUri();
        }

        // If we got no body, defer initialization of the stream until ServerRequest::getBody()
        if ('' !== $body && null !== $body) {
            $this->stream = Stream::create($body);
        }

        $this->withCookieParams($_COOKIE);

        parse_str($this->uri->getQuery(), $query);
        $this->withQueryParams($query);

        $this->withUploadedFiles($_FILES);

        $this->withParsedBody($_REQUEST);
    }

    public function getRequestTarget(): string
    {
        return $this->requestTarget;
    }

    public function withRequestTarget($requestTarget): self
    {
        $this->requestTarget = $requestTarget;

        return $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function withMethod($method): self
    {
        if (!is_string($method)) {
            throw new InvalidArgumentException("Invalid method '$method'");
        }

        $this->method = strtoupper($method);

        return $this;
    }

    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    public function withUri(UriInterface $uri, $preserveHost = false): self
    {
        if ($uri === $this->uri) {
            return $this;
        }

        $this->uri = $uri;

        if (!$preserveHost || !$this->hasHeader('Host')) {
            $this->updateHostFromUri();
        }

        return $this;
    }

    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }

    public function withCookieParams(array $cookies): self
    {
        $this->cookieParams = $cookies;

        return $this;
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    public function withQueryParams(array $query): self
    {
        $this->queryParams = $query;

        return $this;
    }

    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    public function withUploadedFiles(array $uploadedFiles): self
    {
        $this->uploadedFiles = $uploadedFiles;

        return $this;
    }

    public function getParsedBody(): array|object|null
    {
        return $this->parsedBody;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function withParsedBody($data): self
    {
        if (!\is_array($data) && !\is_object($data) && null !== $data) {
            throw new InvalidArgumentException('First parameter to withParsedBody MUST be object, array or null');
        }

        $this->parsedBody = $data;

        return $this;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute($name, $default = null): mixed
    {
        if (false === \array_key_exists($name, $this->attributes)) {
            return $default;
        }

        return $this->attributes[$name];
    }

    public function withAttribute($name, $value): self
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    public function withoutAttribute($name): self
    {
        if (!array_key_exists($name, $this->attributes)) {
            return $this;
        }

        unset($this->attributes[$name]);

        return $this;
    }

    //

    protected function updateHostFromUri(): void
    {
        if ('' === $host = $this->uri->getHost()) {
            return;
        }

        if (null !== ($port = $this->uri->getPort())) {
            $host .= ':' . $port;
        }

        if (isset($this->headerNames['host'])) {
            $header = $this->headerNames['host'];
        } else {
            $this->headerNames['host'] = $header = 'Host';
        }

        // Ensure Host is the first header.
        // See: http://tools.ietf.org/html/rfc7230#section-5.4
        $this->headers = [$header => [$host]] + $this->headers;
    }
}
