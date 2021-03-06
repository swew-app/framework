<?php

declare(strict_types=1);

namespace SWEW\Framework\Base\Traits;

use SWEW\Framework\Base\BaseDTO;
use SWEW\Framework\Http\Request;
use SWEW\Framework\Http\Response;
use SWEW\Framework\SwewApplication;

trait AppActionsTrait
{
    // TODO: private
    public SwewApplication $app;

    final public function setApp(SwewApplication $app): void
    {
        $this->app = $app;
    }

    final public function req(): Request
    {
        return $this->app->req;
    }

    final public function res(BaseDTO|array|string $data = null): Response
    {
        $this->app->res->setRawData($data);

        return $this->app->res;
    }
}
