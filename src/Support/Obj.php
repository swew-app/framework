<?php

namespace Swew\Framework\Support;

class Obj
{
    public static function getPublicVars(mixed $object): array
    {
        return get_object_vars($object);
    }
}
