<?php

namespace Swew\Framework\Support;

final class Str
{
    private function __construct()
    {
    }

    public static function camelCase(string $str): string
    {
        $wordWithSpace = ucwords(
            preg_replace(
                '/[-_\/]+/',
                ' ',
                strtolower($str)
            )
        );

        return lcfirst(
            preg_replace(
                '/\s+/',
                '',
                $wordWithSpace
            )
        );
    }
}
