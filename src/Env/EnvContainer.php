<?php

declare(strict_types=1);

namespace Swew\Framework\Env;

use Swew\Framework\Base\AbstractCacheState;

final class EnvContainer extends AbstractCacheState
{
    private array $envVars = [];

    public function get(string $key, mixed $default = null): mixed
    {
        if ($this->isUseCache) {
            $this->loadCacheFile();

            $this->setMultiple($this->cachedData);
        }

        if ($key === '*') {
            return $this->envVars;
        }

        return $this->envVars[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->envVars[$key] = $value;

        $this->addCacheItem($key, $value);
    }

    private function addCacheItem(string $key, mixed $value): void
    {
        $this->cachedData[$key] = $value;

        $this->isNeedWriteCache = true;
    }

    public function loadGlobalEnvs(): void
    {
        $this->setMultiple(getenv());
    }

    public function setMultiple(array $list)
    {
        foreach ($list as $key => $val) {
            $this->set($key, $val);
        }
    }

    public function loadFromFile(string $filePath): void
    {
        if (! is_readable($filePath) || is_dir($filePath)) {
            throw new \Exception("Env file path exception: '${filePath}' ");
        }

        $fileContent = file_get_contents($filePath);

        $this->parse($fileContent);
    }

    public function parse(string $text): void
    {
        $lines = preg_split('/\n/', $text);

        foreach ($lines as $line) {
            $str = $this->getStringWithoutComment($line);

            $str = trim($str);

            if (empty($str)) {
                continue;
            }

            $this->addVarByString($str);
        }
    }

    private function addVarByString(string $str): void
    {
        [$key, $val] = preg_split('/=/', $str);
        $key = trim($key);
        $val = trim($val);

        if (empty($key)) {
            throw new \Exception("Passed empty 'key' in '${str}'");
        }
        if (empty($val)) {
            throw new \Exception("Passed empty 'value' in '${str}'");
        }

        $this->set($key, $this->convert($val));
    }

    public function convert(string $value): mixed
    {
        if (is_numeric($value)) {
            if (preg_match('/\./', $value)) {
                return floatval($value);
            }

            return intval($value);
        }

        switch (strtolower($value)) {
            case 'true':
                return true;

            case 'false':
                return false;

            case 'null':
                return null;

            default:
                return $this->stripQuotes($value);
        }
    }

    private function stripQuotes(string $value): string
    {
        if (
            ($value[0] === '"' && substr($value, -1) === '"')
            || ($value[0] === "'" && substr($value, -1) === "'")
        ) {
            return substr($value, 1, -1);
        }

        return $value;
    }

    private function getStringWithoutComment(string $str): string
    {
        return preg_split('/#/', $str, 2)[0];
    }
}