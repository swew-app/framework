<?php

namespace Swew\Framework\Support;

final class Arr
{
    private function __construct() {}

    public static function get(array $arr, string $key): mixed
    {
        if (str_contains($key, '.')) {
            $val = &$arr;

            foreach (explode('.', $key) as $k) {
                $val = &$val[$k];
            }

            return $val;
        }

        return null;
    }
}
