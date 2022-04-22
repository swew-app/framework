<?php

declare(strict_types=1);

namespace Integration\BaseCase\stubs\DTO;

use SWEW\Framework\Base\BaseDTO;

final class PostDTO extends BaseDTO
{
    public bool $saved = false;

    public int|null $id = null;

    public string $text = '';

    public function castTypes(): array
    {
        return [
            'id' => fn($id) => intval($id),
        ];
    }

    public function rules(): array
    {
        return [
            'saved' => '',
            'id' => 'required',
            'text' => 'required',
        ];
    }
}
