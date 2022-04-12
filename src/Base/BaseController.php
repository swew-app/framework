<?php

declare(strict_types=1);

namespace SWEW\Framework\Base;

use SWEW\Framework\Http\Request;
use SWEW\Framework\Http\Response;
use SWEW\Framework\SwewApplication;
use Symfony\Component\HttpFoundation\ParameterBag;

abstract class BaseController
{
    public SwewApplication $app;

    public ParameterBag $params;

    final public function setApp(SwewApplication $app): void
    {
        $this->app = $app;
    }

    final public function req(BaseDTO $dto = null): Request|BaseDTO
    {
        if ($dto) {
            $dto->setData($this);

            return $dto;
        }

        return $this->app->req;
    }

    final public function res(BaseDTO|array|string $dto = null): Response
    {
        $this->app->res->setRawData($dto);

        return $this->app->res;
    }
}
