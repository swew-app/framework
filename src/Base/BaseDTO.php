<?php

declare(strict_types=1);

namespace SWEW\Framework\Base;

abstract class BaseDTO
{
    public bool $isDTO = true;

    abstract public function setData(BaseController $controller): void;

    abstract public function getData(): array;

    abstract public function getRules(): array;

    abstract public function validate(): bool;
}
