<?php

declare(strict_types=1);

namespace Swew\Framework\Http\Partials;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

class MessageMethods implements MessageInterface
{
    protected string $protocol = '1.1';

    /** @var array<array-key, string[]> Map of all registered headers, as original name => array of values */
    protected array $headers = [];

    /** @var array<array-key, string> Map of lowercase header name => original name at registration */
    protected array $headerNames = [];

    protected ?StreamInterface $stream = null;

    /**
     * @return string
     */
    public function getProtocolVersion(): string
    {
        return $this->protocol;
    }

    /**
     * @param $version
     * @return $this
     */
    public function withProtocolVersion($version): self
    {
        if ($this->protocol === $version) {
            return $this;
        }

        $this->protocol = $version;

        return $this;
    }

    /**
     * @return array<array-key, string[]>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasHeader(string $name): bool
    {
        return isset($this->headerNames[$this->normalize($name)]);
    }

    /**
     * @param string $name
     * @return array<array-key, string>
     */
    public function getHeader(string $name): array
    {
        $name = $this->normalize($name);

        if (!isset($this->headerNames[$name])) {
            return [];
        }

        $name = $this->headerNames[$name];

        return $this->headers[$name];
    }

    public function getHeaderLine(string $name): string
    {
        return \implode(', ', $this->getHeader($name));
    }

    /**
     * @param string $name
     * @param string|array<array-key, string> $value
     * @return $this
     */
    public function withHeader(string $name, $value): self
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

    /**
     * @param $name
     * @return $this
     */
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
            $this->stream = Stream::create();
        }

        return $this->stream;
    }

    /**
     * @param StreamInterface $body
     * @return $this
     */
    public function withBody(StreamInterface $body): self
    {
        if ($body === $this->stream) {
            return $this;
        }

        $this->stream = $body;

        return $this;
    }


    // Helper
    protected function normalize(string $name): string
    {
        return strtolower($name);
    }
}
