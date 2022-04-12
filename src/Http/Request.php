<?php

declare(strict_types=1);

namespace SWEW\Framework\Http;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

final class Request extends SymfonyRequest
{

    public ParameterBag $params;

    public function setParams(array $params): void
    {
        $this->params = new ParameterBag($params);
    }

    /**
     * Retrieve an input item from the request.
     *
     * @param string|null $key
     * @param mixed|null $default
     * @return mixed
     */
    public function input(string $key = null, mixed $default = null): mixed
    {
        $data = $this->request->all() + $this->query->all();

        if ($key) {
            return $data[$key] ?? $default;
        }

        return $data;
    }
}
