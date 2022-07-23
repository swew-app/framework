<?php

declare(strict_types=1);


use Swew\Framework\Http\Request;
use Swew\Framework\Http\RequestWrapper;
use Swew\Framework\Http\Response;
use Swew\Framework\Http\ResponseWrapper;

function req(): Request
{
    return new RequestWrapper();
}

function res(?string $data = null): Response
{
    $response = new ResponseWrapper();

    if (is_string($data)) {
        $response->setBody($data);
    }

    return $response;
}
