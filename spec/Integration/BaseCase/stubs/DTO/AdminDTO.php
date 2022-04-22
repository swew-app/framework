<?php

declare(strict_types=1);

namespace Integration\BaseCase\stubs\DTO;

use SWEW\Framework\Base\BaseController;
use SWEW\Framework\Base\BaseDTO;

class AdminDTO extends BaseDTO
{
    public string $name = '';

    public string $text = '';

    public function rules(): array
    {
        return [
            'name' => 'required',
            'text' => '',
        ];
    }

    public function messages(): array
    {
        return [
          'required' => 'Field :attribute is required!!!',
        ];
    }
}
