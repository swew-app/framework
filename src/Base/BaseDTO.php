<?php

declare(strict_types=1);

namespace SWEW\Framework\Base;

use Rakit\Validation\Validator;
use SWEW\Framework\Support\Obj;

abstract class BaseDTO
{
    final public function isDTO(): bool
    {
        return true;
    }

    private string $dtoMessage = '';

    final public function setMessage(string $dtoMessage)
    {
        $this->dtoMessage = $dtoMessage;
    }

    final public function getMessage(): string
    {
        return $this->dtoMessage;
    }

    public function rules(): array
    {
        return [];
    }

    final public function setData(array $data): void
    {
        if ($this->isNeedValidate()) {
            $this->validate($data);
            return;
        }

        $this->setDataWithCast($data);
    }

    public function getData(): array
    {
        return Obj::getPublicVars($this);
    }

    private function setDataWithCast(array $data): void
    {

        $currentData = $this->getData();

        foreach ($currentData as $key => $value) {
            if (isset($data[$key])) {
                // TODO: cast types
                $this->$key = $data[$key];
            }
        }
    }

    # region [validation]

    private bool $isValid = true;

    final public function isValid(): bool
    {
        return $this->isValid;
    }

    private string $dtoErrorMessage = '';

    final public function setErrorMessage(string $errorMessage): void
    {
        $this->dtoErrorMessage = $errorMessage;
    }

    final public function getErrorMessage(): string
    {
        return $this->dtoErrorMessage;
    }

    private array $dtoValidationErrors = [];

    final public function getErrors(): array
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

    final public function isNeedValidate(): bool
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
