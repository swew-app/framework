<?php

namespace SWEW\Framework\Base;

use SWEW\Framework\Base\Traits\AppActionsTrait;
use SWEW\Framework\SwewApplication;

abstract class BaseMiddleware
{
    use AppActionsTrait;

    /**
     * Method called before run controller
     *
     * @return bool
     */
    abstract public function handle(): bool;

    /**
     * Method called before return response
     *
     * @return void
     */
    abstract function beforeResponse(): void;
}
