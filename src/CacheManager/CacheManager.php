<?php

declare(strict_types=1);

namespace Swew\Framework\CacheManager;

final class CacheManager
{
    private static ?CacheManager $instance = null;

    private string $cacheDir = __DIR__;

    private bool $isEnabled = false;

    private array $state = [];

    private string $instanceCacheFilePath = __DIR__ . DIRECTORY_SEPARATOR . '_instance.cache';

    private function __construct()
    {
        if ($this->hasInstanceCache()) {
            if (! file_exists($this->instanceCacheFilePath)) {
                return;
            }

            $data = require $this->instanceCacheFilePath;

            if (! is_array($data)) {
                return;
            }

            if (\array_key_exists('cacheDir', $data)) {
                $this->cacheDir = $data['cacheDir'];
            }
            if (\array_key_exists('isEnabled', $data)) {
                $this->isEnabled = $data['isEnabled'];
            }
            if (\array_key_exists('state', $data)) {
                $this->state = $data['state'];
            }
        }
    }

    public static function removeInstance(): void
    {
        self::$instance = null;
    }

    public static function getInstance(bool $forceNew = false): self
    {
        if (self::$instance === null || $forceNew) {
            return new self();
        }

        return self::$instance;
    }

    public function hasInstanceCache(): bool
    {
        return file_exists($this->instanceCacheFilePath);
    }

    public function instanceCache(bool $isEnabled): void
    {
        if ($isEnabled === false) {
            if (file_exists($this->instanceCacheFilePath)) {
                unlink($this->instanceCacheFilePath);
            }
            return;
        }

        $data = [
            'cacheDir' => $this->cacheDir,
            'isEnabled' => $this->isEnabled,
            'state' => $this->state,
        ];

        $content = "<?php\n\ndeclare(strict_types=1);\n\nreturn " . var_export($data, true) . ";\n";

        file_put_contents($this->instanceCacheFilePath, $content);
    }

    public function setCacheDir(string $cacheDir): void
    {
        $this->cacheDir = $cacheDir;
    }

    public function setFile(string $key, string $pathToFileInCacheDir, bool $forceEnabled = false): bool
    {
        if (\array_key_exists($key, $this->state)) {
            return false;
        }

        $this->state[$key] = [
            'path' => preg_replace('#/+#', '/', $this->cacheDir . DIRECTORY_SEPARATOR . $pathToFileInCacheDir),
            'enabled' => $forceEnabled ?: $this->isEnabled,
        ];

        return true;
    }

    public function getFile(string $key): ?string
    {
        $item = \array_key_exists($key, $this->state) ? $this->state[$key] : null;

        if ($item === null || ! $item['enabled']) {
            return null;
        }

        return $item['path'];
    }

    public function enable(string $key = ''): void
    {
        if ($key === '') {
            foreach ($this->state as &$value) {
                $value['enabled'] = true;
            }
        } else {
            if (isset($this->state[$key])) {
                $this->state[$key]['enabled'] = true;
            }
        }
    }

    public function disable(string $key = ''): void
    {
        if ($key === '') {
            foreach ($this->state as &$value) {
                $value['enabled'] = false;
            }
        } else {
            if (isset($this->state[$key])) {
                $this->state[$key]['enabled'] = false;
            }
        }
    }
}
