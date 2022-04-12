<?php

declare(strict_types=1);

namespace SWEW\Framework\Traits;

use SWEW\Framework\Http\Response;

trait CreateResponseTrait
{
    public function createResponse(): Response
    {
        return new Response();
    }
}
