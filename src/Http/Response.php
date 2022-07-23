<?php

declare(strict_types=1);

namespace SWEW\Framework\Http;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class Response implements ResponseInterface
{
    /** @var array Map of standard HTTP status code/reason phrases */
    private const PHRASES = [
        100 => 'Continue', 101 => 'Switching Protocols', 102 => 'Processing',
        200 => 'OK', 201 => 'Created', 202 => 'Accepted', 203 => 'Non-Authoritative Information', 204 => 'No Content', 205 => 'Reset Content', 206 => 'Partial Content', 207 => 'Multi-status', 208 => 'Already Reported',
        300 => 'Multiple Choices', 301 => 'Moved Permanently', 302 => 'Found', 303 => 'See Other', 304 => 'Not Modified', 305 => 'Use Proxy', 306 => 'Switch Proxy', 307 => 'Temporary Redirect',
        400 => 'Bad Request', 401 => 'Unauthorized', 402 => 'Payment Required', 403 => 'Forbidden', 404 => 'Not Found', 405 => 'Method Not Allowed', 406 => 'Not Acceptable', 407 => 'Proxy Authentication Required', 408 => 'Request Time-out', 409 => 'Conflict', 410 => 'Gone', 411 => 'Length Required', 412 => 'Precondition Failed', 413 => 'Request Entity Too Large', 414 => 'Request-URI Too Large', 415 => 'Unsupported Media Type', 416 => 'Requested range not satisfiable', 417 => 'Expectation Failed', 418 => 'I\'m a teapot', 422 => 'Unprocessable Entity', 423 => 'Locked', 424 => 'Failed Dependency', 425 => 'Unordered Collection', 426 => 'Upgrade Required', 428 => 'Precondition Required', 429 => 'Too Many Requests', 431 => 'Request Header Fields Too Large', 451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error', 501 => 'Not Implemented', 502 => 'Bad Gateway', 503 => 'Service Unavailable', 504 => 'Gateway Time-out', 505 => 'HTTP Version not supported', 506 => 'Variant Also Negotiates', 507 => 'Insufficient Storage', 508 => 'Loop Detected', 511 => 'Network Authentication Required',
    ];

    private string $protocol = '1.1';

    /** @var array Map of all registered headers, as original name => array of values */
    private array $headers = [];

    /** @var array Map of lowercase header name => original name at registration */
    private array $headerNames = [];

    private ?StreamInterface $stream = null;

    private int $statusCode = 200;

    private string $reasonPhrase = '';

    /**
     * @param int $status Status code
     * @param array $headers Response headers
     * @param string|StreamInterface|null $body Response body
     * @param string $version Protocol version
     * @param string|null $reason Reason phrase (when empty a default will be used based on the status code)
     * @throws Exception
     */
    public function __construct(
        int    $status = 200,
        array  $headers = [],
        mixed  $body = null,
        string $version = '1.1',
        string $reason = null
    )
    {
        // If we got no body, defer initialization of the stream until Response::getBody()
        if ('' !== $body && !is_null($body)) {
            $this->stream = Stream::create($body);
        }

        $this->statusCode = $status;

        foreach ($headers as $name => $text) {
            $this->withHeader($name, $headers);
        }

        if (null === $reason && isset(self::PHRASES[$this->statusCode])) {
            $this->reasonPhrase = self::PHRASES[$status];
        } else {
            $this->reasonPhrase = $reason ?? '';
        }

        $this->protocol = $version;
    }

    public function getProtocolVersion(): string
    {
        return $this->protocol;
    }

    public function withProtocolVersion($version): self
    {
        if ($this->protocol === $version) {
            return $this;
        }

        $this->protocol = $version;

        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function hasHeader($name): bool
    {
        return isset($this->headerNames[$this->normalize($name)]);
    }

    public function getHeader($name): array
    {
        $name = $this->normalize($name);

        if (!isset($this->headerNames[$name])) {
            return [];
        }

        $name = $this->headerNames[$name];

        return $this->headers[$name];
    }

    public function getHeaderLine($name): string
    {
        return \implode(', ', $this->getHeader($name));
    }

    /**
     * @param string $name
     * @param array<array-key, string>|string $value
     * @return $this
     */
    public function withHeader($name, $value): self
    {
        $normalized = $this->normalize($name);

        if (!is_array($value)) {
            $value = [$value];
        }

        if (isset($this->headerNames[$normalized])) {
            unset($this->headers[$this->headerNames[$normalized]]);
        }
        $this->headerNames[$normalized] = $name;
        $this->headers[$name] = $value;

        return $this;
    }

    /**
     * @param string $name
     * @param array<array-key, string>|string $value
     * @return $this
     */
    public function withAddedHeader($name, $value): self
    {
        if (!is_array($value)) {
            $value = [$value];
        }

        /** @var  array<array-key, string> $list */
        $list = $this->getHeader($name);

        if (is_array($value)) {
            $list = array_merge($list, $value);
        } else {
            $list[] = $value;
        }

        $this->withHeader($name, $list);

        return $this;
    }

    public function withoutHeader($name): self
    {
        $normalized = $this->normalize($name);

        if (!isset($this->headerNames[$normalized])) {
            return $this;
        }

        $name = $this->headerNames[$normalized];

        unset($this->headers[$name], $this->headerNames[$normalized]);

        return $this;
    }

    public function getBody(): StreamInterface
    {
        if (null === $this->stream) {
            $this->stream = Stream::create('');
        }

        return $this->stream;
    }

    public function withBody(StreamInterface $body): self
    {
        if ($body === $this->stream) {
            return $this;
        }

        $this->stream = $body;

        return $this;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function withStatus($code, $reasonPhrase = ''): self
    {
        if (!\is_int($code) && !\is_string($code)) {
            throw new \InvalidArgumentException('Status code has to be an integer');
        }

        $code = (int)$code;
        if ($code < 100 || $code > 599) {
            throw new \InvalidArgumentException(\sprintf('Status code has to be an integer between 100 and 599. A status code of %d was given', $code));
        }

        $new = clone $this;
        $new->statusCode = $code;
        if ((null === $reasonPhrase || '' === $reasonPhrase) && isset(self::PHRASES[$new->statusCode])) {
            $reasonPhrase = self::PHRASES[$new->statusCode];
        }
        $new->reasonPhrase = $reasonPhrase;

        return $new;
    }

    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    //

    private function normalize(string $name): string
    {
        return strtr($name, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz');
    }
}
