<?php

declare(strict_types=1);

namespace Integration\BaseCase\stubs\DTO;

use SWEW\Framework\Base\BaseController;
use SWEW\Framework\Base\BaseDTO;

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
            'id' => $controller->app->req->params->get('postId'),
            'text' => $controller->app->req->request->get('text'),
        ];
    }

    public function getData(): array
    {
        return $this->data;
    }
}
