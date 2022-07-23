<?php

declare(strict_types=1);

namespace Swew\Framework\Http;

use Swew\Framework\Http\Partials\Stream;

final class ResponseWrapper extends Response
{
    private static ?ResponseWrapper $instance = null;

    public function __construct(int $status = 200, array $headers = [], mixed $body = null, string $version = '1.1', string $reason = null)
    {
        if (!is_null(ResponseWrapper::$instance)) {
            return ResponseWrapper::$instance;
        }

        parent::__construct($status, $headers, $body, $version, $reason);

        ResponseWrapper::$instance = $this;
    }

    /**
     * Sends HTTP headers and content.
     *
     * @return $this
     */
    public function send(): ResponseWrapper
    {
        $this->sendHeaders();
        $this->sendContent();

        if (\function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } elseif (\function_exists('litespeed_finish_request')) {
            litespeed_finish_request();
        }

        return $this;
    }

    public function sendHeaders(): void
    {
        $headers = $this->getHeaders();

        foreach ($headers as $name => $values) {
            header(
                $name . ': ' . implode(',', $values),
                true,
                $this->getStatusCode()
            );
        }
    }

    public function sendContent(): void
    {
        echo $this->getBody();
    }

    public function setBody(string $data): self
    {
        $stream = Stream::create($data);

        return $this->withBody($stream);
    }
}
