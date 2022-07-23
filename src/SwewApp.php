<?php

declare(strict_types=1);

namespace Swew\Framework;

use Swew\Framework\Container\Container;
use Swew\Framework\Env\EnvContainer;
use Swew\Framework\Manager\AppMiddlewareManager;
use Swew\Framework\Middleware\MiddlewarePipeline;
use Swew\Framework\Router\Router;

class SwewApp
{
    private bool $DEV = true;

    public string $host = '';

    protected ?string $cacheDir = null;

    protected string $pageNotFound = '';

    protected string $pageServerError = '';

    /**
     * Path to router files
     *
     * @example
     *  $routers = [
     *      __DIR__ . '/../router/router.php',
     * ];
     */
    protected array $routeFiles = [];

    /**
     * @example
     *  $middlewares = [
     *    'auth' => /Features/Common/Middleware/AuthMiddleware::class,
     *  ];
     */
    protected array $middlewares = [];

    /**
     * List of Middleware names that apply to all routers
     *
     * @example
     *  $globalMiddlewares = [ 'auth' ];
     */
    protected array $globalMiddlewares = [];

    /**
     * Path to the features folder
     *
     * @example
     *  $features = __DIR__ . '/../Features';
     */
    protected string $features = '';

    private ?Router $router = null;

    public function __construct()
    {
        /** @var EnvContainer $env */
        $env = env();

        /** @var Container $container */
        $container = container();

        if (!is_null($this->cacheDir)) {
            $env->useCache(true, $this->cacheDir . '/env_cache.php');
            $container->useCache(true, $this->cacheDir . '/container_cache.php');
        }

        $this->host = $env->get('host', '');

        $this->initRouter();
        $route = $this->findRoute();

        if (is_null($route)) {
            $this->showErrorPage();
            return;
        }

        $this->runPipeline($route);

        $res = res();

        // TODO: показываем полученную страницу
    }

    /**
     * @throws \Exception
     */
    private function initRouter(): void
    {
        $this->router = new Router(
            Router::getRoutesFromPaths($this->routeFiles),
            $this->host
        );

        if ($this->DEV) {
            $this->router->validate();
        }
    }

    private function findRoute(): array|null
    {
        $req = req();

        if (is_null($this->router)) {
            return null;
        }

        $routeItem = $this->router->getRoute(
            $req->getMethod(),
            $req->getUri()->getPath()
        );

        if (empty($routeItem['class']) || empty($routeItem['method'])) {
            return null;
        }

        /** @var array $attr */
        $attr = $routeItem['params'];

        foreach ($attr as $k => $v) {
            $req->withAttribute($k, $v);
        }

        return $routeItem;
    }

    private function runPipeline(array $route): void
    {
        $appMiddlewareManager = new AppMiddlewareManager(
            $this->middlewares,
            $this->globalMiddlewares
        );

        $middlewares = $appMiddlewareManager->getAppMiddlewares(
            $route['class'],
            $route['method'],
            $route['middlewares']
        );

        $pipeline = new MiddlewarePipeline($middlewares);

        $pipeline->handle(req()); // Запускаяем цепочку Middlewares
    }

    private function showErrorPage(): void
    {
        // TODO
    }
}
