<?php

namespace SWEW\Framework\Base;

use SWEW\Framework\SwewApplication;

abstract class BaseMiddleware
{
    protected SwewApplication $app;

    abstract public function handle(): bool;

    /**
     * Method called before return response
     *
     * @return void
     */
    public function beforeResponse(): void {}
}
