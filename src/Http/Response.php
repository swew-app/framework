<?php

declare(strict_types=1);

namespace Swew\Framework\Http;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Swew\Framework\Http\Partials\MessageMethods;
use Swew\Framework\Http\Partials\Stream;

class Response extends MessageMethods implements ResponseInterface
{
    /** @var array Map of standard HTTP status code/reason phrases */
    protected const PHRASES = [
        100 => 'Continue', 101 => 'Switching Protocols', 102 => 'Processing',
        200 => 'OK', 201 => 'Created', 202 => 'Accepted', 203 => 'Non-Authoritative Information', 204 => 'No Content', 205 => 'Reset Content', 206 => 'Partial Content', 207 => 'Multi-status', 208 => 'Already Reported',
        300 => 'Multiple Choices', 301 => 'Moved Permanently', 302 => 'Found', 303 => 'See Other', 304 => 'Not Modified', 305 => 'Use Proxy', 306 => 'Switch Proxy', 307 => 'Temporary Redirect',
        400 => 'Bad Request', 401 => 'Unauthorized', 402 => 'Payment Required', 403 => 'Forbidden', 404 => 'Not Found', 405 => 'Method Not Allowed', 406 => 'Not Acceptable', 407 => 'Proxy Authentication Required', 408 => 'Request Time-out', 409 => 'Conflict', 410 => 'Gone', 411 => 'Length Required', 412 => 'Precondition Failed', 413 => 'Request Entity Too Large', 414 => 'Request-URI Too Large', 415 => 'Unsupported Media Type', 416 => 'Requested range not satisfiable', 417 => 'Expectation Failed', 418 => 'I\'m a teapot', 422 => 'Unprocessable Entity', 423 => 'Locked', 424 => 'Failed Dependency', 425 => 'Unordered Collection', 426 => 'Upgrade Required', 428 => 'Precondition Required', 429 => 'Too Many Requests', 431 => 'Request Header Fields Too Large', 451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error', 501 => 'Not Implemented', 502 => 'Bad Gateway', 503 => 'Service Unavailable', 504 => 'Gateway Time-out', 505 => 'HTTP Version not supported', 506 => 'Variant Also Negotiates', 507 => 'Insufficient Storage', 508 => 'Loop Detected', 511 => 'Network Authentication Required',
    ];

    protected int $statusCode = 200;

    protected string $reasonPhrase = '';

    /**
     * @param  int  $status Status code
     * @param  array  $headers Response headers
     * @param  string|StreamInterface|null  $body Response body
     * @param  string  $version Protocol version
     * @param  string|null  $reason Reason phrase (when empty a default will be used based on the status code)
     *
     * @throws Exception
     */
    public function __construct(
        int $status = 200,
        array $headers = [],
        mixed $body = null,
        string $version = '1.1',
        ?string $reason = null
    ) {
        // If we got nobody, defer initialization of the stream until Response::getBody()
        if ('' !== $body && ! is_null($body)) {
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

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function withStatus(int $code, string $reasonPhrase = ''): self
    {
        if ($code < 100 || $code > 599) {
            throw new Exception(\sprintf('Status code has to be an integer between 100 and 599. A status code of %d was given', $code));
        }

        $this->statusCode = $code;
        if ('' === $reasonPhrase && isset(self::PHRASES[$this->statusCode])) {
            $reasonPhrase = self::PHRASES[$this->statusCode];
        }
        $this->reasonPhrase = $reasonPhrase;

        return $this;
    }

    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }
}
