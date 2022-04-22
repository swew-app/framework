<?php


use SWEW\Framework\Base\BaseDTO;

class StubMessageDTO extends BaseDTO
{
    public string $message = 'Hello DTO';
}

class StubValidateDTO extends BaseDTO
{
    public string $message = 'Hello DTO';

    public function rules(): array
    {
        return [
            'message' => 'required|min:5'
        ];
    }
}

function dto_stub(string $type)
{
    return match ($type) {
        'message' => new StubMessageDTO(),
        'validate' => new StubValidateDTO(),
        default => throw new Exception('Available stub types: "message", "validate"'),
    };
}
