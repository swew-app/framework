<?php

declare(strict_types=1);

namespace SWEW\Framework\DTO;

use SWEW\Framework\Controller\BaseController;

abstract class BaseDTO
{
    public bool $isDTO = true;

    abstract public function setData(BaseController $controller): void;

    abstract public function getData(): array;

    abstract public function getRules(): array;

    abstract public function validate(): bool;
}
