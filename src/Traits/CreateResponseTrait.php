<?php

namespace SWEW\Framework\Traits;

use SWEW\Framework\Http\Response;

trait CreateResponseTrait
{
    protected Response $response;

    public function createResponse(): Response
    {
        return new Response();
    }

}
