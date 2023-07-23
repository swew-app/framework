<?php

declare(strict_types=1);

namespace Swew\Framework\Hook;

class Hook
{
    private static array $callbackList = [];

    private function __construct()
    {
    }

    public static function on(HK $hookKey, \Closure $callback): void
    {
        self::$callbackList[] = [
            'key' => $hookKey,
            'callback' => $callback,
        ];
    }

    public static function call(HK $hookKey, mixed ...$args): void
    {
        foreach (self::$callbackList as $item) {
            if ($item['key'] === $hookKey) {
                $item['callback'](...$args);
            }
        }
    }
}
