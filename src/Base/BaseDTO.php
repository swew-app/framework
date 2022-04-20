<?php

declare(strict_types=1);

namespace SWEW\Framework\Base;

use Rakit\Validation\Validator;

abstract class BaseDTO
{
    public bool $isDTO = true;

    public bool $isValid = true;

    public array $errors = [];

    protected mixed $data = null;

    abstract public function rules(): array|null;

    public function setData(mixed $data): void
    {
        $this->data = $data;
    }

    public function getData(): array
    {
        return [
            'data' => $this->data,
            'errors' => $this->errors,
        ];
    }

    public function messages(): array
    {
        return [];
    }

    public function validate(): bool
    {
        $rules = $this->rules();

        if ($rules) {
            $validator = new Validator();
            $messages = method_exists($this, 'messages') ? $this->messages() : [];

            if (method_exists($this, 'translation')) {
                $translation = $this->translation() ;
                $validator->setTranslation($translation);
            }

            $validation = $validator->validate($this->data, $rules, $messages);

            $this->isValid = !$validation->fails();

            if ($this->isValid === false) {
                $this->errors = $validation->errors->firstOfAll();
            }

            $this->setData(
                $validation->getValidData()
            );
        }


        return $this->isValid;
    }
}
