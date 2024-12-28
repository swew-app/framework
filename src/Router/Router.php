<?php

declare(strict_types=1);

namespace Swew\Framework\Router;

use Exception;
use ReflectionClass;
use FastRoute\Dispatcher as FastRouteDispatcher;
use Swew\Framework\Support\Str;
use Swew\Framework\Router\Methods\Get;

use function FastRoute\simpleDispatcher;

class Router
{
    private array $allowedKeys = [
        'name',
        'path',
        'controller',
        'middlewares',
        'method',
        'children',
        'dev',
        'methodAsPath',
        'collector',
    ];

    private string $basePath = '/';

    public array $routes = [];

    public string $host = '';

    public function __construct(
        array $routes,
        string $host = ''
    ) {
        foreach ($routes as $route) {
            $this->addRoute($route);
        }

        $this->host = $host;
    }

    public function setBasePath(string $basePath): void
    {
        $this->basePath = $basePath;
    }

    public function addRoute(array|Route $route): void
    {
        $item = $route instanceof Route ? $route->toArray() : $route;

        if (array_key_exists('collector', $route)) {
            $routes = $this->getRouteFromCollector($route);
            foreach ($routes as $r) {
                $this->routes[] = $r;
            }
        } else {
            $this->routes[] = $item;
        }
    }

    /**
     * @throws Exception
     */
    public function validate(): bool
    {
        // validate child structure before create router list
        foreach ($this->routes as $route) {
            if (isset($route['children'])) {
                $this->validateChildren($route['children']);
            }
        }

        $route_keys = [];

        $routes = $this->getRoutes();

        foreach ($routes as $route) {
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
    private function validateChildren(array $childrenRoutes): void
    {
        foreach ($childrenRoutes as $route) {
            if (!is_array($route)) {
                throw new Exception("Route must be Array. Got '$route'");
            }

            $this->isValidRoute($route);

            if (isset($route['children'])) {
                $this->validateChildren($route['children']);
            }
        }
    }


    /**
     * @throws Exception
     */
    private function isValidRoute(array $route): bool
    {
        if (!array_key_exists('collector', $route)) {
            if (!array_key_exists('name', $route)) {
                throw new Exception("Route key 'name' is required");
            }

            if (!array_key_exists('controller', $route)) {
                throw new Exception("Route key 'controller' is required");
            }
        }

        foreach ($route as $key => $val) {
            if (!in_array($key, $this->allowedKeys)) {
                throw new Exception("Not allowed key '{$key}' in router");
            }
        }

        return true;
    }

    public function getRoutes(): array
    {
        $list = $this->makeChildRoutes($this->basePath, $this->routes);

        foreach ($list as &$route) {
            $route['path'] = preg_replace('/\/+/', '/', $route['path']);
            if (isset($route['children'])) {
                unset($route['children']);
            }
        }

        return $list;
    }

    private function makeChildRoutes(string $prefix, array $routes): array
    {
        $list = [];

        foreach ($routes as $route) {
            $route['path'] = $prefix . $route['path'];

            $list[] = $route;

            if (isset($route['children'])) {
                $childRoutes = $this->makeChildRoutes($route['path'], $route['children']);
                $list = array_merge($list, $childRoutes);
            }
        }

        return $list;
    }

    /**
     * Method for CLI route list
     *
     * @return string[][]
     */
    public function getInfoList(): array
    {
        $list = [
            ['Name', 'Path', 'Controller', 'Middlewares', 'DEV'],
        ];

        $routes = $this->getRoutes();

        foreach ($routes as $route) {
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
        if (empty($httpMethod)) {
            throw new \LogicException('Router: passed empty method');
        }
        if (empty($uri)) {
            throw new \LogicException('Router: passed empty uri');
        }
        return $this->findRouteByFastRouter($httpMethod, $uri);
    }

    /**
     * Create url link path
     *
     * @throws Exception
     */
    public function url(string $routeName, array $params = []): string
    {
        $routes = $this->getRoutes();

        foreach ($routes as $route) {
            if ($route['name'] === $routeName) {
                $path = $route['path'];
                break;
            }
        }

        if (!isset($path)) {
            throw new Exception("Route with name: '{$routeName}' not found");
        }

        /** @var string $path */
        $path = preg_replace_callback(
            '/\{([^:]+)(.+)?\}/i',
            function ($matches) use ($params) {
                if (!key_exists($matches[1], $params)) {
                    throw new Exception('Router->url $params[' . $matches[1] . '] not found');
                }
                return $params[$matches[1]];
            },
            $path
        );

        return $this->host . $path;
    }

    # region [Internal methods]
    public function findRouteByFastRouter(string $httpMethod, string $uri): array
    {
        $routes = $this->getRoutes();

        $dispatcher = simpleDispatcher(function ($r) use ($routes) {
            foreach ($routes as $route) {
                $method = $route['method'] ?? 'GET|HEAD|OPTIONS';

                # /Path/To/Class::class|methodName OR /Path/To/Class::class OR /Path/To/Class::class@_method_as_path_
                $handlerItem = is_array($route['controller'])
                    ? implode('@', $route['controller'])
                    : (empty($route['methodAsPath']) ? $route['controller'] : $route['controller'] . '@_method_as_path_');

                $middlewares = $route['middlewares'] ?? [];
                $handlerItem = $handlerItem . '|' . implode('|', $middlewares);

                $r->addRoute(explode('|', $method), $route['path'], $handlerItem);
                # DEMO: $r->addRoute('POST', $route['path'], $handlerItem);
            }
        });

        $uri = $this->normalizeUri($uri);

        $found = $dispatcher->dispatch($httpMethod, $uri);

        if ($found[0] !== FastRouteDispatcher::FOUND) {
            // Empty
            $lastSlashPosition = strrpos($uri, '/');
            if ($lastSlashPosition) {
                $firstPart = substr($uri, 0, $lastSlashPosition);
                $lastPart = substr($uri, $lastSlashPosition + 1);

                $found = $dispatcher->dispatch($httpMethod, $firstPart);

                return $this->toRouteFromFastRoute($found, $httpMethod, $lastPart);
            }
        }

        return $this->toRouteFromFastRoute($found, $httpMethod, $uri);
    }

    /**
     * @param array $param [ FastRouterStatus, Controller@Method, [params]]
     * @param string $httpMethod
     * @param string $uri
     * @return array
     * @throws Exception
     */
    private function toRouteFromFastRoute(array $param, string $httpMethod, string $uri): array
    {
        if ($param[0] !== FastRouteDispatcher::FOUND) {
            return [];
        }

        $items = explode('|', $param[1], 2);

        $classAndMethod = $items[0];
        $middlewares = $items[1] ?? '';

        $classAndMethodArray = explode('@', $classAndMethod);

        $method = $classAndMethodArray[1] ?? 'getIndex';

        if ($method === '_method_as_path_') {
            $method = $this->getMethodByUri($httpMethod, $uri);
        }

        return [
            'class' => $classAndMethodArray[0],
            'method' => $method,
            'params' => $param[2] ?? [],
            'middlewares' => array_filter(explode('|', $middlewares)),
        ];
    }

    /**
     * @param string $httpMethod GET
     * @param string $uri /blog
     * @return string
     * @throws Exception
     */
    public function getMethodByUri(string $httpMethod, string $uri): string
    {
        if (!in_array($httpMethod, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'])) {
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
    private function normalizeUri(string $uri): string
    {
        if (false !== $pos = strpos($uri, '?')) {
            return substr($uri, 0, $pos);
        }
        return $uri;
    }
    # endregion

    /**
     * Get merged router configs
     *
     * @param array $routeConfigPaths
     * @return array
     */
    public static function getRoutesFromPaths(array $routeConfigPaths): array
    {
        $list = array_map(
            fn($path) => include ($path),
            $routeConfigPaths
        );

        return array_merge(...array_values($list));
    }

    private function getRouteFromCollector(array $route): array
    {
        $className = $route['collector'];
        $reflection = new ReflectionClass($className);

        $resultRoutes = [];

        foreach ($reflection->getMethods() as $method) {
            $attributes = $method->getAttributes(Get::class);

            if (count($attributes) > 0) {
                foreach ($attributes as $attribute) {
                    $instance = $attribute->newInstance();
                    $middlewares = [
                        ...($route['middlewares'] ?? []),
                        ...$instance->getMiddlewares()
                    ];

                    $resultRoutes[] = [
                        ...$route,
                        'path' => $instance->getPath(),
                        'controller' => [$className, $method->getName()],
                        'method' => $instance->getMethod(),
                        'name' => $instance->getName() ?: $method->getName(),
                        'middlewares' => $middlewares,
                    ];
                }
            }
        }

        return $resultRoutes;
    }
}
