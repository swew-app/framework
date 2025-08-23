<?php

declare(strict_types=1);

namespace Swew\Framework\Http;

use Exception;
use RuntimeException;
use Swew\Framework\Http\Partials\Stream;

use function function_exists;

final class ResponseWrapper extends Response
{
    private static ?ResponseWrapper $instance = null;

    private string|array|null $storeData = null;

    private bool $isTest = false;

    private array $cookies = [];

    private function __construct(int $status = 200, array $headers = [], mixed $body = null, string $version = '1.1', ?string $reason = null)
    {
        parent::__construct($status, $headers, $body, $version, $reason);

        self::$instance = $this;
    }

    public static function getInstance(int $status = 200, array $headers = [], mixed $body = null, string $version = '1.1', ?string $reason = null): self
    {
        if (is_null(self::$instance)) {
            return new self($status, $headers, $body, $version, $reason);
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
     * Sends HTTP headers and content.
     *
     * @return $this
     *
     * @throws Exception
     */
    public function send(): self
    {
        $this->sendHeaders();
        $this->sendContent();

        if (function_exists('fastcgi_finish_request')) {
            \fastcgi_finish_request();
        }
        //  elseif (function_exists('litespeed_finish_request')) {
        //     \litespeed_finish_request();
        // }

        return $this;
    }

    public function sendHeaders(): void
    {
        foreach ($this->cookies as $name => $data) {
            $name = htmlspecialchars($name);
            $value = htmlspecialchars((string) $data['value']);

            if (! $this->isTest) {
                setcookie($name, $value, $data['options']);
            } else {
                if ($data['options']['expires'] > time()) {
                    $_COOKIE[$name] = $value;
                }
            }
        }

        if ($this->isTest) {
            return;
        }

        $headers = $this->getHeaders();

        foreach ($headers as $name => $values) {
            header(
                $name . ': ' . implode(',', $values),
                true,
                $this->getStatusCode(),
            );
        }
    }

    /**
     * @throws Exception
     */
    public function sendContent(): void
    {
        if ($this->isTest) {
            return;
        }

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

    /**
     * Need to prevent content rendering during tests
     */
    public function setTestEnv(bool $isTest): void
    {
        $this->isTest = $isTest;
    }

    public function setStoredData(string|array $data): void
    {
        $this->storeData = $data;
    }

    public function getStoredData(): string|array|null
    {
        return $this->storeData;
    }

    public function setCookie(string $name, string $value, int $expires = 0, string $path = '/', bool $httponly = true, ?array $options = null): self
    {
        $this->cookies[$name] = [
            'value' => $value,
            'options' =>
                $options ?? [
                    'expires' => $expires > 0 ? $expires : (time() + 1209600), // 14 day
                    'path' => $path,
                    'domain' => $_SERVER['HTTP_ORIGIN'] ?? '',
                    'secure' => $_SERVER['HTTPS'] ?? false,
                    'httponly' => $httponly,
                ]
            ,
        ];

        return $this;
    }

    public function removeCookie(string $name): bool
    {
        if (isset($this->cookies[$name])) {
            $this->setCookie($name, '', time() - 100000);

            return true;
        }

        return false;
    }

    public function redirect(string $url, int $status = 300): self
    {
        $this->withStatus($status);
        $this->withHeader('Location', $url);

        return $this;
    }

    private string $viewFileName = '';

    private array $viewData = [];

    public function view(string $viewFileName, array $viewData = []): self
    {
        $this->viewFileName = $viewFileName;
        $this->viewData = $viewData;

        return $this;
    }

    public function getViewFileName(): string
    {
        return $this->viewFileName;
    }

    public function getViewData(): array
    {
        return $this->viewData;
    }

    private string|bool $viewRaw = false;

    public function raw(mixed $raw): self
    {
        if (is_string($raw)) {
            $handler = fopen($raw, 'r');
            if ($handler === false) {
                throw new RuntimeException("Failed to open file: {$raw}");
            }

            try {
                $this->viewRaw = stream_get_contents($handler);
            } finally {
                fclose($handler);
            }
        } else {
            $this->viewRaw = stream_get_contents($raw);
        }

        return $this;
    }

    public function getRaw(): string|bool
    {
        return $this->viewRaw;
    }
}
