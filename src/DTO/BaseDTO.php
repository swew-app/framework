<?php

declare(strict_types=1);

namespace SWEW\Framework\DTO;

abstract class BaseDTO
{
    private mixed $rawData = null;

    public function setData($data): void
    {
        $this->rawData = $data;
    }

    abstract public function getData(): array;

    abstract public function getRules(): array;

    abstract public function validate(): bool;
}
