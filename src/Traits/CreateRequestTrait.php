<?php

namespace SWEW\Framework\Traits;

use SWEW\Framework\Http\Request;

trait CreateRequestTrait
{
    protected Request $request;

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
