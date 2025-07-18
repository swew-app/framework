<?php

declare(strict_types=1);

namespace Swew\Framework\Router;

use Exception;

class RouteHelper
{
    private string $name = '';

    private string $path = '';

    private string $prefix = '';

    private string $method = 'GET|POST|PUT|PATCH|DELETE|OPTIONS|HEAD|CONNECT|TRACE';

    private array $middlewares = [];

    private string|array $controller = '';

    private string $collector = '';

    private array $children = [];

    public function name(string $name): self
    {
        if (! empty($this->collector)) {
            throw new Exception("You cannot specify 'name' if you have already specified the 'collector' key.");
        }

        $this->name = $name;

        return $this;
    }

    public function path(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function prefix(string $pathPrefix): self
    {
        $this->prefix = $pathPrefix;

        return $this;
    }

    public function method(string $method): self
    {
        if ($method === 'any') {
            $method = 'GET|POST|PUT|PATCH|DELETE|OPTIONS|HEAD|CONNECT|TRACE';
        }

        $this->method = $method;

        return $this;
    }

    public function middlewares(array $middlewares): self
    {
        $this->middlewares += $middlewares;

        return $this;
    }

    public function controller(string|array $class): self
    {
        if (! empty($this->collector)) {
            throw new Exception("You cannot specify 'controller' if you have already specified the 'collector' key.");
        }

        $this->controller = $class;

        return $this;
    }

    public function collector(string $collector): self
    {
        if (! empty($this->name) || ! empty($this->controller)) {
            throw new Exception("You cannot specify 'collector' if you have already specified the 'name' or 'controller' key.");
        }

        $this->collector = $collector;

        return $this;
    }

    public function children(array $child): self
    {
        $this->children = $child;

        return $this;
    }

    /**
     * @psalm-param 'name' $keys
     */
    public function toArray(string ...$keys): array
    {
        $path = str_replace('//', '/', $this->prefix.'/'.$this->path);

        $route = [
            'name' => empty($this->name) ? $this->slug($path) : $this->name,
            'path' => $path,
            'method' => $this->method,
            'middlewares' => $this->middlewares,
        ];

        if ($this->controller) {
            $route['controller'] = $this->controller;
        }

        if ($this->collector) {
            $route['collector'] = $this->collector;
        }

        if (count($this->children)) {
            $route['children'] = $this->children;
        }

        if (count($keys) > 0) {
            $route = array_filter(
                $route,
                fn ($key) => in_array($key, $keys),
                ARRAY_FILTER_USE_KEY
            );
        }

        return $route;
    }

    public function slug(string $string): string
    {
        // Переводим строку в нижний регистр
        $string = strtolower($string);
        // Заменяем пробелы и нежелательные символы на дефисы
        $string = preg_replace('/\W+/', '-', $string);
        $string = preg_replace('/-+/', '-', $string);
        // Удаляем начальные и конечные дефисы
        return trim($string, '-');
    }
}
