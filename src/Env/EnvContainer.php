<?php

declare(strict_types=1);

namespace Swew\Framework\Env;

use Swew\Framework\Base\AbstractCacheState;
use Swew\Framework\Support\Arr;

final class EnvContainer extends AbstractCacheState
{
    private static ?EnvContainer $instance = null;

    private array $envVars = [];

    private function __construct()
    {
        if (isset(self::$instance)) {
            return self::$instance;
        }

        self::$instance = $this;
    }

    public static function removeInstance(): void
    {
        self::$instance = null;
    }

    public static function getInstance($forceNew = false): self
    {
        if (is_null(self::$instance) || $forceNew) {
            return new self();
        }

        return self::$instance;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        // TODO: сделать конвертацию типов по значению
        if ($this->isUseCache) {
            $this->loadCache();

            $this->setMultiple($this->cachedData);
        }

        if ($key === '*') {
            return $this->envVars;
        }

        if (!str_contains($key, '.')) {
            return $this->envVars[$key] ?? $default;
        }

        return Arr::get($this->envVars, $key) ?? $default;
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
        if (!is_readable($filePath) || is_dir($filePath)) {
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

        return match (strtolower($value)) {
            'true' => true,
            'false' => false,
            'null' => null,
            default => $this->stripQuotes($value),
        };
    }

    private function stripQuotes(string $value): string
    {
        if (
            ($value[0] === '"' && str_ends_with($value, '"'))
            || ($value[0] === "'" && str_ends_with($value, "'"))
        ) {
            return substr($value, 1, -1);
        }

        return $value;
    }

    private function getStringWithoutComment(string $str): string
    {
        return explode('#', $str, 2)[0];
    }
}
