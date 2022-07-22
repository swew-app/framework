<?php

namespace SWEW\Framework\Support;

class Str
{
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
