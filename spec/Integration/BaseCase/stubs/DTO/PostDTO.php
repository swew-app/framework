<?php

declare(strict_types=1);

namespace Integration\BaseCase\stubs\DTO;

use SWEW\Framework\Base\BaseController;
use SWEW\Framework\Base\BaseDTO;

final class PostDTO extends BaseDTO
{
    public function rules(): array
    {
        return [];
    }

    public function getData(): array
    {
        return $this->data;
    }
}
