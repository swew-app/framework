<?php

namespace Swew\Framework\Router;

class Route
{
    private string $name = '';
    private string $path = '';
    private string $prefix = '';

    private string $method = 'GET|POST|PUT|PATCH|DELETE|OPTIONS|HEAD|CONNECT|TRACE';

    private array $middlewares = [];

    private string|array $controller = '';

    private array $children = [];

    public bool $methodAsPath = false;

    public bool $isDev = false;

    public function name(string $name): self
    {
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
        $this->controller = $class;
        return $this;
    }

    public function children(array $child): self
    {
        $this->children = $child;
        return $this;
    }

    public function methodAsPath(bool $methodAsPath): self
    {
        $this->methodAsPath = $methodAsPath;
        return $this;
    }

    public function isDev(bool $isDev = true): self
    {
        $this->isDev = $isDev;

        return $this;
    }

    public function toArray(...$keys): array
    {
        $path = str_replace('//', '/', $this->prefix . '/' .  $this->path);

        $route = [
            'name' => empty($this->name) ? $this->slug($path) : $this->name,
            'path' =>  $path,
            'method' => $this->method,
            'middlewares' => $this->middlewares,
            'controller' => $this->controller,
        ];

        if (count($this->children)) {
            $route['children'] = $this->children;
        }

        if ($this->isDev) {
            $route['dev'] = true;
        }

        if ($this->methodAsPath) {
            $route['methodAsPath'] = true;
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
