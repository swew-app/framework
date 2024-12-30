<?php

namespace Swew\Framework\Support;

class Obj
{
    public static function getPublicVars(\Swew\Framework\Base\BaseDTO $object): array
    {
        return get_object_vars($object);
    }
}
