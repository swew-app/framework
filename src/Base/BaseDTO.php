<?php

declare(strict_types=1);

namespace Swew\Framework\Base;

use Rakit\Validation\Validator;
use Swew\Framework\Support\Obj;

abstract class BaseDTO
{
    public function rules(): array
    {
        return [];
    }

    public function getData(): array
    {
        return Obj::getPublicVars($this);
    }

    public function setData(array $data): void
    {
        $this->setDataWithCast($data);
    }

    /**
     * Type casting for setData method.
     * The types are given via a key lookup.
     *
     * @example
     * public function castTypes(): array
     * {
     *      return [
     *          'login' => fn (mixed $login) => strtolower($login),
     *      ];
     * }
     *
     *
     * @return array
     */
    public function castTypes(): array
    {
        return [];
    }

    private function setDataWithCast(array $data): void
    {
        $currentData = $this->getData();
        $casts = $this->castTypes();

        foreach ($currentData as $key => $value) {
            if (isset($data[$key])) {
                if (isset($casts[$key])) {
                    $this->$key = $casts[$key]($data[$key]);

                    continue;
                }

                $this->$key = match (gettype($currentData[$key])) {
                    'boolean' => (bool)$data[$key],
                    'integer' => (int)$data[$key],
                    'double' => (float)$data[$key],
                    'string' => (string)$data[$key],
                    default => $data[$key],
                };
            }
        }
    }


    # region [validation]

    private bool $isValid = true;

    public function isValid(): bool
    {
        return $this->isValid;
    }

    private string $dtoErrorMessage = '';

    public function setErrorMessage(string $errorMessage): void
    {
        $this->dtoErrorMessage = $errorMessage;
    }

    public function getErrorMessage(): string
    {
        return $this->dtoErrorMessage;
    }

    private array $dtoValidationErrors = [];

    public function getErrors(): array
    {
        return $this->dtoValidationErrors;
    }

    /**
     * Validation messages
     *
     * @return array
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * Validate translations
     *
     * @return array
     */
    public function translation(): array
    {
        return [];
    }

    public function isNeedValidate(): bool
    {
        return count($this->rules()) > 0;
    }

    public function validate(?array $data = null): bool
    {
        if ($this->isNeedValidate() === false) {
            return $this->isValid();
        }

        $data = $data ?? $this->getData();
        $rules = $this->rules();
        $messages = $this->messages();
        $translation = $this->translation();
        $validator = new Validator();

        if (count($translation) > 0) {
            $validator->setTranslations($translation);
        }

        $validation = $validator->validate($data, $rules, $messages);

        $this->isValid = !$validation->fails();

        $this->dtoValidationErrors = $validation->errors->firstOfAll();

        $this->setDataWithCast(
            $validation->getValidData()
        );

        return $this->isValid;
    }

    # endregion
}
