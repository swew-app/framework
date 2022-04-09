<?php

declare(strict_types=1);

namespace SWEW\Framework\Traits;

use SWEW\Framework\DTO\BaseDTO;
use SWEW\Framework\Http\Response;

trait CreateResponseTrait
{
    public Response $response;

    public function createResponse(): Response
    {
        return new Response();
    }

    public function view()
    {
        // TODO: добавить шаблонизатор
        return $this;
    }
}
