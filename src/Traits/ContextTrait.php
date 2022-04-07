<?php

namespace SWEW\Framework\Traits;

use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

trait ContextTrait
{
    protected LoggerInterface|null $logger;

    protected CacheInterface|null $cache;

    protected array $contextMap = [];
}
