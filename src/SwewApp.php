<?php

declare(strict_types=1);

namespace Swew\Framework;

use Swew\Framework\Container\Container;
use Swew\Framework\Env\EnvContainer;
use Swew\Framework\Hook\HK;
use Swew\Framework\Hook\Hook;
use Swew\Framework\Manager\AppMiddlewareManager;
use Swew\Framework\Manager\FeatureManager;
use Swew\Framework\Middleware\MiddlewarePipeline;
use Swew\Framework\Router\Router;

class SwewApp
{
    protected bool $DEV = true;

    protected bool $TEST = false;

    public string $host = '';

    protected ?string $cacheDir = null;

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

    public ?Router $router = null;

    public function __construct()
    {
        Hook::call(HK::beforeInit);

        /** @var EnvContainer $env */
        $env = env();
        $env->loadGlobalEnvs();

        /** @var Container $container */
        $container = container();

        $this->TEST = (bool)$env->get('APP_IS_TEST', false);

        if (!is_null($this->cacheDir) && !$this->TEST) {
            $env->useCache(true, $this->cacheDir . '/env_cache.php');
            $container->useCache(true, $this->cacheDir . '/container_cache.php');
        }

        $this->DEV = (bool)$env->get('APP_IS_DEV', false);

        $this->host = $env->get('host', '');

        res()->setTestEnv($this->TEST);

        set_error_handler(function ($e) {
            Hook::call(HK::onError, $e);
            $this->errorHandler($e);
        });
        set_exception_handler(function ($e) {
            Hook::call(HK::onError, $e);
            $this->errorHandler($e);
        });

        FeatureManager::setFeaturePath($this->features);
    }

    final public function run(): void
    {
        Hook::call(HK::beforeRun);

        $this->initRouter();

        $route = $this->findRoute();

        if (is_null($route)) {
            // Route not found
            $route = [
                'class' => fn() => $this->makeErrorPage(404, 'Page not found.')
            ];
        } else {
            FeatureManager::setController($route['class']);
        }

        Hook::call(HK::beforeHandlePipeline);

        $this->runPipeline($route);

        Hook::call(HK::afterHandlePipeline);

        $statusCode = res()->getStatusCode();

        if (200 <= $statusCode && $statusCode < 300) {
            res()->getBody()->write(FeatureManager::getPreparedResponse());
        } else {
            $this->makeErrorPage($statusCode);
        }

        Hook::call(HK::beforeSend);

        res()->send();

        Hook::call(HK::afterSend);
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

        $middlewares = $appMiddlewareManager->getMiddlewaresForApp(
            $route['class'],
            $route['method'],
            $route['middlewares']
        );

        $pipeline = new MiddlewarePipeline($middlewares);

        $pipeline->handle(req()); // Запускаяем цепочку Middlewares
    }

    public function errorHandler(mixed $e): void
    {
        // Handle error
        if ($e instanceof \Exception || $e instanceof \Error) {
            throw $e;
        }
        die();
    }

    /**
     * Error page for production
     */
    public function makeErrorPage(int $status, string $message = ''): void
    {
        res($message)->withStatus($status);

        if ($status == 404) {
            res()->view('error/404.php');
        }
    }
}
