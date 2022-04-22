<?php

declare(strict_types=1);

namespace SWEW\Framework\Traits;

use SWEW\Framework\Http\Response;
use SWEW\Framework\SwewApplication;

trait CreateResponseTrait
{
    public function createResponse(): Response
    {
        $response = new Response();

        $response->init($this);

        return  $response;
    }
}
