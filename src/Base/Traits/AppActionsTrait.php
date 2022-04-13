<?php

declare(strict_types=1);

namespace SWEW\Framework\Base\Traits;

use SWEW\Framework\Base\BaseDTO;
use SWEW\Framework\Http\Request;
use SWEW\Framework\Http\Response;
use SWEW\Framework\SwewApplication;

trait AppActionsTrait
{
    public SwewApplication $app;

    final public function setApp(SwewApplication $app): void
    {
        $this->app = $app;
    }

    final public function req(BaseDTO $dto): Request|BaseDTO
    {
        $dto->setData($this);
        return $dto;
    }

    final public function res(BaseDTO|array|string $dto = null): Response
    {
        $this->app->res->setRawData($dto);

        return $this->app->res;
    }
}
