<?php

declare(strict_types=1);

namespace Swew\Framework\Base;

abstract class AbstractCacheState
{
    /**
     * @var array<string, mixed>
     */
    protected array $cachedData = [];

    protected string $cacheFilePath = '';

    protected bool $isUseCache = false;

    protected bool $isCacheFileLoaded = false;

    protected bool $isNeedWriteCache = false;

    public function __destruct()
    {
        if ($this->isUseCache && $this->isNeedWriteCache) {
            if ($this->cacheFilePath === '') {
                throw new \Error("Set cache file '{$this->cacheFilePath}' ");
            }
            $this->writeCacheFile();
        }
    }

    public function useCache(bool $isUseCache, string $cacheFilePath): void
    {
        $this->isUseCache = $isUseCache;
        $this->cacheFilePath = $cacheFilePath;

        if (file_exists($this->cacheFilePath) === false) {
            $this->isNeedWriteCache = true;
        }
    }

    public function getCacheData(): array
    {
        return $this->cachedData;
    }

    public function clearCache(): void
    {
        if (! empty($this->cacheFilePath) && file_exists($this->cacheFilePath)) {
            unlink($this->cacheFilePath);
        }
    }

    private function writeCacheFile(): void
    {
        $content = "<?php\ndeclare(strict_types=1);\nreturn " . var_export($this->getCacheData(), true) . ";\n";

        $hash1 = md5($content);
        $fileContents = is_readable($this->cacheFilePath) ? file_get_contents($this->cacheFilePath) : '';
        $hash2 = $fileContents !== false ? md5($fileContents) : '';

        if ($hash1 !== $hash2) {
            file_put_contents($this->cacheFilePath, $content);
        }
    }

    protected function loadCache(): void
    {
        if ($this->isCacheFileLoaded) {
            return;
        }

        $cacheFilePath = $this->cacheFilePath;

        $data = (function () use ($cacheFilePath): array {
            try {
                if (file_exists($cacheFilePath)) {
                    $data = include $cacheFilePath;

                    if (! is_array($data)) {
                        throw new \Exception();
                    }

                    return $data;
                }

                $this->isNeedWriteCache = true;

                return [];
            } catch (\Exception) {
                $this->clearCache();

                return [];
            }
        })();

        $this->cachedData = array_merge($this->cachedData, $data);

        $this->isCacheFileLoaded = true;
    }
}
