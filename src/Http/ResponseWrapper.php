<?php

declare(strict_types=1);

namespace Swew\Framework\Http;

use Exception;
use Swew\Framework\Http\Partials\Stream;

final class ResponseWrapper extends Response
{
    private static ?ResponseWrapper $instance = null;

    public function __construct(int $status = 200, array $headers = [], mixed $body = null, string $version = '1.1', string $reason = null)
    {
        if (!is_null(self::$instance)) {
            return self::$instance;
        }

        parent::__construct($status, $headers, $body, $version, $reason);

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
     * Sends HTTP headers and content.
     *
     * @return $this
     * @throws Exception
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

    /**
     * @throws Exception
     */
    public function sendContent(): void
    {
        if (connection_aborted() !== CONNECTION_NORMAL) {
            throw new Exception('Connection Aborted');
        }

        echo $this->getBody();
    }

    public function setBody(string $data): self
    {
        $this->stream = Stream::create($data);

        return $this->withBody($this->stream);
    }

    private string $viewFileName = '';

    public function view(string $viewFileName): void
    {
        $this->viewFileName = $viewFileName;
    }

    public function getViewFileName(): string
    {
        return $this->viewFileName;
    }
}
