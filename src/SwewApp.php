<?php

declare(strict_types=1);

namespace Swew\Framework;

use Exception;
use Swew\Framework\Container\Container;
use Swew\Framework\Env\EnvContainer;
use Swew\Framework\Hook\HK;
use Swew\Framework\Hook\Hook;
use Swew\Framework\Manager\AppMiddlewareManager;
use Swew\Framework\Manager\FeatureManager;
use Swew\Framework\Middleware\MiddlewarePipeline;
use Swew\Framework\Router\Router;
use Throwable;

class SwewApp
{
    public readonly bool $DEV;

    public string $host = '';
    public ?Router $router = null;
    public readonly EnvContainer $env;
    public readonly Container $container;
    protected string $rootPath = '';
    protected string $envFilePath = '';
    protected ?string $cacheDir = null;
    protected ?string $preloadClass = null;
    /**
     * Path to the features folder
     *
     * @example
     *  $features = __DIR__ . '/../Features';
     */
    protected string $features = '';
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

    public function __construct()
    {
        if (!is_null($this->preloadClass)) {
            new $this->preloadClass($this);
        }

        Hook::call(HK::beforeInit, $this);

        $this->env = env();
        $this->env->loadGlobalEnvs();
        $this->container = container();

        $IS_TEST = (bool)$this->env->get('__TEST__', false);

        if (!is_null($this->cacheDir) && !$IS_TEST) {
            $this->env->useCache(true, $this->cacheDir . '/env_cache.php');
            $this->container->useCache(true, $this->cacheDir . '/container_cache.php');
        }

        if (
            !empty($this->envFilePath) &&
            !$this->env->get('__LOADED_ENV_FILE__', false)
        ) {
            // If we use cache, this block will be skipped
            $this->env->loadFromFile($this->envFilePath);
            $this->env->set('__LOADED_ENV_FILE__', true);
        }

        $this->env->set('APP_ROOT', realpath($this->rootPath));

        $this->DEV = (bool)$this->env->get('APP_IS_DEV', false) || $IS_TEST;

        $this->host = $this->env->get('host', '');

        res()->setTestEnv($IS_TEST);

        if (!$IS_TEST) {
            set_exception_handler(function (Throwable $e) {
                $this->exceptionHandler($e);
            });
        }

        FeatureManager::setFeaturePath($this->features);
    }

    final public function run(): void
    {
        Hook::call(HK::beforeRun, $this);

        $this->initRouter();

        $route = $this->findRoute();

        if (is_null($route)) {
            $this->makeErrorPage(404, 'Page not found.');
        } else {
            FeatureManager::setController($route['class']);

            Hook::call(HK::beforeHandlePipeline);

            $this->runPipeline($route);

            Hook::call(HK::afterHandlePipeline);

            $statusCode = res()->getStatusCode();

            if ($statusCode >= 200 && $statusCode < 300) {
                res()->getBody()->write(FeatureManager::getPreparedResponse());
            } else {
                $this->makeErrorPage($statusCode);
            }
        }

        Hook::call(HK::beforeSend);

        res()->send();

        Hook::call(HK::afterSend);
    }

    /**
     * @throws Exception
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
            $route['middlewares'] ?? []
        );

        $pipeline = new MiddlewarePipeline($middlewares);

        $pipeline->handle(req()); // Запускаяем цепочку Middlewares
    }

    /**
     * Method for displaying the error page
     *
     * @param int $status
     * @param string $message
     * @return void
     */
    public function makeErrorPage(int $status, string $message = ''): void
    {
        if ($status === 404) {
            res('Page not found')->view('404.php');
        }

        if ($status >= 500) {
            res('Page not found')->view('500.php');
        }
    }

    public function exceptionHandler(Throwable $exception): void
    {
        throw $exception;
    }
}
