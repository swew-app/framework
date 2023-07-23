<?php

declare(strict_types=1);

namespace Swew\Framework\Http\Partials;

use Psr\Http\Message\StreamInterface;

class Stream implements StreamInterface
{
    /** @var resource|null A resource reference */
    private static mixed $stream = null;

    private bool $seekable = false;

    private bool $readable = false;

    private bool $writable = false;

    /** @var array|mixed|void|bool|null */
    private mixed $uri = null;

    private ?int $size = null;

    /** @var array Hash of readable and writable stream types */
    private const READ_WRITE_HASH = [
        'read' => [
            'r' => true, 'w+' => true, 'r+' => true, 'x+' => true, 'c+' => true,
            'rb' => true, 'w+b' => true, 'r+b' => true, 'x+b' => true,
            'c+b' => true, 'rt' => true, 'w+t' => true, 'r+t' => true,
            'x+t' => true, 'c+t' => true, 'a+' => true,
        ],
        'write' => [
            'w' => true, 'w+' => true, 'rw' => true, 'r+' => true, 'x+' => true,
            'c+' => true, 'wb' => true, 'w+b' => true, 'r+b' => true,
            'x+b' => true, 'c+b' => true, 'w+t' => true, 'r+t' => true,
            'x+t' => true, 'c+t' => true, 'a' => true, 'a+' => true,
        ],
    ];

    private static ?Stream $instance = null;

    private function __construct()
    {
        if (Stream::$instance) {
            return Stream::$instance;
        }
        Stream::$instance = $this;
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
     * Creates a new PSR-7 stream.
     *
     * @param StreamInterface|string $body
     *
     * @return StreamInterface
     */
    public static function create(StreamInterface|string $body = ''): StreamInterface
    {
        if ($body instanceof StreamInterface) {
            return $body;
        }

        if (is_null(Stream::$stream)) {
            $resource = fopen('php://memory', 'rw+');

            if ($resource === false) {
                throw new \LogicException('Can\'t create resource');
            }

            Stream::$stream = $resource;
        }

        if ($body !== '') {
            fwrite(Stream::$stream, $body);
        }

        $new = new self();

        $meta = \stream_get_meta_data(Stream::$stream);
        $new->seekable = $meta['seekable'] && 0 === \fseek(Stream::$stream, 0, \SEEK_CUR);
        $new->readable = isset(self::READ_WRITE_HASH['read'][$meta['mode']]);
        $new->writable = isset(self::READ_WRITE_HASH['write'][$meta['mode']]);

        return $new;
    }

    /**
     * Closes the stream when the destructed.
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if ($this->isSeekable()) {
            $this->seek(0);
        }

        return $this->getContents();
    }

    public function close(): void
    {
        if (!is_null(Stream::$stream)) {
            /** @var resource $stream */
            $stream = Stream::$stream;

            if (is_resource($stream)) {
                fclose($stream);
                Stream::$stream = null;
            }
            $this->detach();
        }
    }

    public function detach()
    {
        if (!isset(Stream::$stream)) {
            return null;
        }

        fclose(Stream::$stream);
        Stream::$stream = null;

        $this->size = $this->uri = null;
        $this->readable = $this->writable = $this->seekable = false;

        return Stream::$stream;
    }

    private function getUri()
    {
        if (false !== $this->uri) {
            $this->uri = $this->getMetadata('uri') ?? false;
        }

        return $this->uri;
    }

    public function getSize(): ?int
    {
        if (null !== $this->size) {
            return $this->size;
        }

        if (!isset(Stream::$stream)) {
            return null;
        }

        // Clear the stat cache if the stream has a URI
        if ($uri = $this->getUri()) {
            \clearstatcache(true, $uri);
        }

        $stats = \fstat(Stream::$stream);
        if (isset($stats['size'])) {
            $this->size = $stats['size'];

            return $this->size;
        }

        return null;
    }

    public function tell(): int
    {
        if (!isset(Stream::$stream)) {
            throw new \RuntimeException('Stream is detached');
        }

        if (false === $result = @\ftell(Stream::$stream)) {
            throw new \RuntimeException('Unable to determine stream position: ' . (\error_get_last()['message'] ?? ''));
        }

        return $result;
    }

    public function eof(): bool
    {
        return !isset(Stream::$stream) || \feof(Stream::$stream);
    }

    public function isSeekable(): bool
    {
        return $this->seekable;
    }

    public function seek($offset, $whence = \SEEK_SET): void
    {
        if (!isset(Stream::$stream)) {
            throw new \RuntimeException('Stream is detached');
        }

        if (!$this->seekable) {
            throw new \RuntimeException('Stream is not seekable');
        }

        if (-1 === \fseek(Stream::$stream, $offset, $whence)) {
            throw new \RuntimeException('Unable to seek to stream position "' . $offset . '" with whence ' . \var_export($whence, true));
        }
    }

    public function rewind(): void
    {
        $this->seek(0);
    }

    public function isWritable(): bool
    {
        return $this->writable;
    }

    public function write(string $string): int
    {
        if (!isset(Stream::$stream)) {
            throw new \RuntimeException('Stream is detached');
        }

        if (!$this->writable) {
            throw new \RuntimeException('Cannot write to a non-writable stream');
        }

        // We can't know the size after writing anything
        $this->size = null;

        if (false === $result = @\fwrite(Stream::$stream, $string)) {
            throw new \RuntimeException('Unable to write to stream: ' . (\error_get_last()['message'] ?? ''));
        }

        return $result;
    }

    public function isReadable(): bool
    {
        return $this->readable;
    }

    public function read($length): string
    {
        if (!isset(Stream::$stream)) {
            throw new \RuntimeException('Stream is detached');
        }

        if (!$this->readable) {
            throw new \RuntimeException('Cannot read from non-readable stream');
        }

        if (false === $result = @\fread(Stream::$stream, $length)) {
            throw new \RuntimeException('Unable to read from stream: ' . (\error_get_last()['message'] ?? ''));
        }

        return $result;
    }

    public function getContents(): string
    {
        if (!isset(Stream::$stream)) {
            throw new \RuntimeException('Stream is detached');
        }

        rewind(Stream::$stream);

        if (false === $contents = @\stream_get_contents(Stream::$stream)) {
            throw new \RuntimeException('Unable to read stream contents: ' . (\error_get_last()['message'] ?? ''));
        }

        return $contents;
    }

    public function getMetadata($key = null): mixed
    {
        if (!isset(Stream::$stream)) {
            return $key ? null : [];
        }

        $meta = \stream_get_meta_data(Stream::$stream);

        if (null === $key) {
            return $meta;
        }

        return $meta[$key] ?? null;
    }
}
