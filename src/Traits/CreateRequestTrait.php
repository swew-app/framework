<?php

declare(strict_types=1);

namespace SWEW\Framework\Traits;

use SWEW\Framework\Http\Request;

trait CreateRequestTrait
{
    public Request $request;

    protected function createRequest(): Request
    {
        return new Request(
            $_GET,
            $_POST,
            [],
            $_COOKIE,
            $_FILES,
            $_SERVER
        );
    }

}
