<?php

declare(strict_types=1);

namespace Integration\BaseCase\stubs\DTO;

use SWEW\Framework\Controller\BaseController;
use SWEW\Framework\DTO\BaseDTO;

final class PostDTO extends BaseDTO
{
    private array $data = [];

    public function getRules(): array
    {
        return [];
    }

    public function validate(): bool
    {
        return true;
    }

    public function setData(BaseController $controller): void
    {
        $this->data = [
            'saved' => true,
            'id' => $controller->params->get('postId'),
            'text' => $controller->req->request->get('text'),
        ];
    }

    public function getData(): array
    {
        return $this->data;
    }
}
