<?php

declare(strict_types=1);

namespace SWEW\Framework\Middleware\Exception;

use DomainException;

final class MiddlewarePipeNextHandlerAlreadyCalledException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Cannot invoke pipeline handler $handler->handle() more than once');
    }
}
