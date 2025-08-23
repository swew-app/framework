<?php

declare(strict_types=1);

namespace Swew\Framework;

use LogicException;
use Swew\Framework\CacheManager\CacheManager;
use Swew\Framework\Container\Container;
use Swew\Framework\Env\EnvContainer;
use Swew\Framework\Http\RequestWrapper;
use Swew\Framework\Http\ResponseWrapper;
use Swew\Framework\Manager\FeatureManager;
use Swew\Framework\Middleware\MiddlewarePipeline;
use Swew\Router\MatchedRouterMiddleware;
use Swew\Router\Router;

abstract class SwewApp
{
    public Container $container;

    public EnvContainer $env;

    protected string $cacheDir = '';

    public ?Router $router = null;

    /**
     * Path to router files
     *
     * @example
     *  $routers = [
     *    __DIR__ . '/../Features/Common/routes.php',
     *  ];
     */
    protected array $routeFiles = [];

    /**
     * @example:
     *  $middlewares = [
     *    'auth' => /Features/Common/Middleware/AuthMiddleware::class,
     *  ];
     */
    protected array $middlewares = [];

    /**
     * List of Middleware names that apply to all routers
     *
     * @example:
     *  $globalMiddlewares = [ 'auth' ];
     */
    protected array $globalMiddlewares = [];

    protected string $containerAutoloadConfigDir = '';

    /**
     * File with cache configs
     *
     * @example:
     * ```
     * <?php
     * // $cacheConfigFile = __DIR__ . '/cache.php';
     * return [
     *     'router' => [
     *         'enabled' => true,
     *         'file' => 'router.cache',
     *     ],
     * ];
     * ```
     */
    protected string $cacheConfigFile = '';

    final public function run(): void
    {
        RequestWrapper::removeInstance();
        ResponseWrapper::removeInstance();

        $routeMiddleware = $this->getRouteMiddleware();

        if ($routeMiddleware->status > 299) {
            // TODO: Not found or invalid
            return;
        }

        $middlewares = array_filter(
            $this->middlewares,
            fn (string $key) => in_array($key, $routeMiddleware->middlewares),
            ARRAY_FILTER_USE_KEY,
        );

        $middlewares['_'] = $routeMiddleware;

        $pipeline = new MiddlewarePipeline($middlewares);

        $pipeline->handle(req()); // Run Middlewares

        $statusCode = res()->getStatusCode();

        if ($statusCode < 300 || $statusCode >= 400) {
            // Non redirect
            res()->getBody()->write(
                FeatureManager::getPreparedResponse($routeMiddleware->result),
            );
        }

        if ($statusCode >= 500) {
            $this->makeErrorPage($statusCode);
        }
    }

    public function load(): self
    {
        $this->loadCache();
        $this->loadEnv();
        $this->loadContainer();
        $this->loadRouter();

        return $this;
    }

    /**
     * Method for displaying the error page
     */
    public function makeErrorPage(int $status, string $message = ''): void
    {
        if ($status === 404) {
            res('Page not found')
                ->withStatus($status);

            // ->view('404.php');
        }

        if ($status >= 500) {
            res('Page not found')
                ->withStatus($status);

            // ->view('500.php');
        }
    }

    public function exceptionHandler(\Throwable $exception): void
    {
        throw $exception;
    }

    protected function loadCache(): void
    {
        if ($this->cacheConfigFile === '') {
            return;
        }

        /** @var array */
        $cacheConfigFile = require_once $this->cacheConfigFile;

        $cache = CacheManager::getInstance();
        $cache->setCacheDir($this->cacheDir);

        foreach ($cache as $key => $value) {
            $cache->setFile($key, $value['file'], $value['enabled']);
        }
    }

    protected function loadEnv(): void
    {
        $cache = CacheManager::getInstance();

        $this->env = env();

        if ($cache->getFile('env')) {
            $this->env->useCache(true, $cache->getFile('env'));
        }
    }

    protected function loadContainer(): void
    {
        $cache = CacheManager::getInstance();

        $this->container = container();

        if ($cache->getFile('container')) {
            $this->container->useCache(true, $cache->getFile('container'));
        }

        if ($this->containerAutoloadConfigDir !== '') {
            $this->container->loadConfigFiles($this->containerAutoloadConfigDir);
        }
    }

    protected function loadRouter(): void
    {
        $cache = CacheManager::getInstance();

        $this->router = Router::getInstance();

        $basePath = (string) $this->env->get('APP_BASE_PATH', '/');
        $this->router->setGlobalPrefix($basePath);

        $this->router->setCache($cache->getFile('router'));

        if (! $this->router->hasCache()) {
            $this->router
                ->addMiddlewares(array_keys($this->middlewares))
                ->globalMiddleware(...$this->globalMiddlewares);

            foreach ($this->routeFiles as $filePath) {
                require_once $filePath;
            }
        }
    }

    protected function getRouteMiddleware(): MatchedRouterMiddleware
    {
        if (is_null($this->router)) {
            throw new LogicException('Router not loaded.');
        }

        $req = RequestWrapper::new();

        $middleware = $this->router->match($req->getMethod(), $req->getUri()->getPath());

        foreach ($middleware->params as $k => $v) {
            $req->withAttribute($k, $v);
        }

        return $middleware;
    }
}
