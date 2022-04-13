<?php

namespace Integration\BaseCase\stubs\middlewares;

use SWEW\Framework\Base\BaseMiddleware;

final class BreakMiddleware extends BaseMiddleware
{
    public function handle(): bool
    {
        $this->res('bad request Man');

        return false;
    }

    public function beforeResponse(): void
    {
    }
}
