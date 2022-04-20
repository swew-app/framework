<?php

declare(strict_types=1);

namespace Integration\BaseCase\stubs\DTO;

use SWEW\Framework\Base\BaseController;
use SWEW\Framework\Base\BaseDTO;

final class AdminDTO extends BaseDTO
{
    public function rules(): array
    {
        return [
            'text' => '',
            'name' => 'required'
        ];
    }

    public function messages(): array
    {
        return [
          'required' => 'Field :attribute is required!!!',
        ];
    }
}
