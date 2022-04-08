<?php

namespace SWEW\Framework\Http;

enum RespType: string
{
    case HTML = 'html';
    case JSON = 'json';

    public function type(): string
    {
        return match ($this) {
            RespType::HTML => RespType::HTML->value,
            RespType::JSON => RespType::JSON->value,
        };
    }
}
