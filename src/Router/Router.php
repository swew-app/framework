<?php

declare(strict_types=1);

namespace SWEW\Framework\Router;

use Exception;
use FastRoute\Dispatcher as FastRouteDispatcher;
use SWEW\Framework\Support\Str;
use function FastRoute\simpleDispatcher;

class Router
{
    private array $allowed_keys = [
        'name',
        'path',
        'controller',
        'middlewares',
        'method',
        'dev',
    ];

    public function __construct(
        public array $routes,
        public string $host = ''
    )
    {
    }

    public function addRoute(array $route)
    {
        $this->routes += $route;
    }

    /**
     * @throws Exception
     */
    public function validate(): bool
    {
        $route_keys = [];

        foreach ($this->routes as $route) {
            if (!$this->isValidRoute($route)) {
                return false;
            }

            if (in_array($route['name'], $route_keys)) {
                throw new Exception("Route name '{$route['name']}' already used");
            }

            $route_keys[] = $route['name'];
        }


        return true;
    }

    /**
     * @throws Exception
     */
    private function isValidRoute(array $route): bool
    {
        if (!array_key_exists('name', $route)) {
            throw new Exception("Route key 'name' is required");
        }

        if (!array_key_exists('controller', $route)) {
            throw new Exception("Route key 'controller' is required");
        }

        foreach ($route as $key => $val) {
            if (!in_array($key, $this->allowed_keys)) {
                throw new Exception("Not allowed key '{$key}' in router");
            }
        }

        return true;
    }

    /**
     * Method for CLI route list
     *
     * @return \string[][]
     */
    public function getInfoList(): array
    {
        $list = [
            ['Name', 'Path', 'Controller', 'Middlewares', 'DEV'],
        ];

        foreach ($this->routes as $route) {
            $list[] = [
                $route['name'],
                $route['path'],
                $route['controller'],
                implode(',', $route['middlewares'] ?? []),
                empty($route['dev']) ? 'FALSE' : 'TRUE',
            ];
        }

        return $list;
    }

    /**
     * Get route creation config
     *
     * @param string $httpMethod
     * @param string $uri
     * @return array
     */
    public function getRoute(string $httpMethod, string $uri): array
    {
        return $this->findRouteByFastRouter($httpMethod, $uri);
    }

    /**
     * Create url link path
     *
     * @throws Exception
     */
    public function url(string $routeName, array $params = []): string
    {
        foreach ($this->routes as $route) {
            if ($route['name'] === $routeName) {
                $path = $route['path'];
                break;
            }
        }

        if (!isset($path)) {
            throw  new Exception("Route with name: '{$routeName}' not found");
        }

        $path = preg_replace_callback(
            '/\{([^:]+)(.+)?\}/i',
            function ($matches) use ($params) {
                if (!key_exists($matches[1], $params)) {
                    throw  new Exception('Router->url $params[' . $matches[1] . '] not found');
                }
                return $params[$matches[1]];
            },
            $path
        );

        return $this->host . $path;
    }

    /**
     * Get merged router configs
     *
     * @param array $routeConfigPaths
     * @return array
     */
    public static function getRoutesFromPaths(array $routeConfigPaths): array
    {
        $list = array_map(
            fn($path) => include($path),
            $routeConfigPaths
        );

        return array_merge(...$list);
    }

    # region [Internal methods]
    public function findRouteByFastRouter(string $httpMethod, string $uri): array
    {
        $routes = $this->routes;

        $dispatcher = simpleDispatcher(function ($r) use ($routes) {
            foreach ($routes as $route) {
                $method = $route['method'] ?? 'GET|HEAD';

                # /Path/To/Class::class|methodName OR /Path/To/Class::class
                $handlerItem = is_array($route['controller'])
                    ? implode('@', $route['controller'])
                    : $route['controller'];

                $middlewares = $route['middlewares'] ?? [];
                $handlerItem = $handlerItem . '|' . implode('|', $middlewares);

                $r->addRoute(explode('|', $method), $route['path'], $handlerItem);
//                $r->addRoute('POST', $route['path'], $handlerItem);

            }
        });

        $uri = $this->normalizeUri($uri);

        $found = $dispatcher->dispatch($httpMethod, $uri);

        return $this->toRouteFromFastRoute($found, $httpMethod, $uri);
    }

    /**
     * @param array $param [ FastRouterStatus, Controller@Method, [params]]
     * @param string $httpMethod
     * @param string $uri
     * @return array
     */
    private function toRouteFromFastRoute(array $param, string $httpMethod, string $uri): array
    {
        if ($param[0] !== FastRouteDispatcher::FOUND) {
            return [];
        }

        [$classAndMethod, $middlewares] = explode('|', $param[1], 2);

        $items = explode('@', $classAndMethod);

        $method = $items[1] ?? $this->getMethodByUri($httpMethod, $uri);

        return [
            'class' => $items[0],
            'method' => $method,
            'params' => $param[2] ?? [],
            'middlewares' => array_filter(explode('|', $middlewares)),
        ];
    }

    /**
     * @param string $httpMethod GET
     * @param string $uri /blog
     * @return string
     */
    public function getMethodByUri(string $httpMethod, string $uri): string
    {
        if (!in_array($httpMethod, ['GET', 'POST', 'PUT', 'DELETE'])) {
            throw new Exception("Wrong http method '$httpMethod'");
        }

        $uri = $this->normalizeUri($uri);

        if ($uri === '' || $uri === '/') {
            $uri = 'index';
        }

        return Str::camelCase($httpMethod . '-' . $uri);
    }

    /**
     * /blog/id?query=HELLO => /blog/id
     *
     * @param string $uri
     * @return string
     */
    public function normalizeUri(string $uri): string
    {
        if (false !== $pos = strpos($uri, '?')) {
            return substr($uri, 0, $pos);
        }
        return $uri;
    }
    # endregion
}
