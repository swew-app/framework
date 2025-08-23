<?php

namespace Swew\Framework\Support;

final class Str
{
    private function __construct() {}

    public static function camelCase(string $str): string
    {
        $wordWithSpace = ucwords(
            (string) preg_replace(
                '/[-_\/]+/',
                ' ',
                strtolower($str),
            ),
        );

        return lcfirst(
            (string) preg_replace(
                '/\s+/',
                '',
                $wordWithSpace,
            ),
        );
    }
}
