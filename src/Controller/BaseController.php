<?php

declare(strict_types=1);

namespace SWEW\Framework\Controller;

use SWEW\Framework\DTO\BaseDTO;
use SWEW\Framework\Http\Response;
use SWEW\Framework\SwewApplication;
use Symfony\Component\HttpFoundation\ParameterBag;

abstract class BaseController
{
    protected SwewApplication $app;

    public ParameterBag $ctx;

    public ParameterBag $params;

    public function __construct(array $params)
    {
        $this->setParams($params);
        $this->setCtx([]);
    }

    public function setParams(array $params): void
    {
        $this->params = new ParameterBag($params);
    }

    public function setCtx(array $ctx): void
    {
        $this->ctx = new ParameterBag($ctx);
    }

    final public function setApp(SwewApplication $app): void
    {
        $this->app = $app;
    }

//    final public function req(?BaseDTO $dto)
//    {
//        if ($dto) {
//            //$this->app->request->query->all();
//            $dto->setData([]);
//
//            return $dto;
//        }
//
//        return $this->app->request;
//    }

    final public function res(BaseDTO|array|string $dto = null): Response
    {
        if ($dto instanceof BaseDTO) {
            $this->app->response->rawData = $dto->getData();
        } else {
            $this->app->response->rawData = $dto;
        }

        return $this->app->response;
    }
}
