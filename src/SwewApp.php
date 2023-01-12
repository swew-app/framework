<?php

declare(strict_types=1);

namespace Swew\Framework;

use Swew\Framework\Base\BaseDTO;
use Swew\Framework\Container\Container;
use Swew\Framework\Env\EnvContainer;
use Swew\Framework\Manager\AppMiddlewareManager;
use Swew\Framework\Middleware\MiddlewarePipeline;
use Swew\Framework\Router\Router;
use Swew\Framework\Support\FeatureDetection;

class SwewApp
{
    private bool $DEV = true;

    private bool $TEST = false;

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

    public ?Router $router = null;

    public function __construct()
    {
        /** @var EnvContainer $env */
        $env = env();
        // TODO: перенести в хук
        $env->loadGlobalEnvs();

        /** @var Container $container */
        $container = container();

        if (!is_null($this->cacheDir)) {
            $env->useCache(true, $this->cacheDir . '/env_cache.php');
            $container->useCache(true, $this->cacheDir . '/container_cache.php');
        }

        $this->host = $env->get('host', '');

        $this->DEV = !$env->get('production', false);
        $this->TEST = !!$env->get('IS_TEST', false);
    }

    public function run(): void
    {
        $this->initRouter();

        $route = $this->findRoute();

        if (is_null($route)) {
            $this->showErrorPage(404);
            return;
        }

        FeatureDetection::setController($route['class']);

        $this->runPipeline($route);

        $statusCode = res()->getStatusCode();

        if (200 <= $statusCode && $statusCode < 300) {
            $this->prepareResponse();
        } else {
            $this->showErrorPage($statusCode);
        }

        if ($this->TEST) {
            return;
        }

        res()->send();
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

    private function findRoute(): array |null
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

    private function showErrorPage(int $status, string $message = ''): void
    {
        // TODO
    }

    private function prepareResponse(): void
    {
        $data = store();

        if (is_null($data)) {
            return;
        }

        if ($data instanceof BaseDTO) {
            $data = $data->getData();
        }

        $viewName = res()->getViewFileName();

        if (req()->isAjax() || empty($viewName)) {
            res()->withHeader('Content-Type', 'application/json');

            if (is_array($data)) {
                $data = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
            }
        } else {
            $filePath = FeatureDetection::getView($this->features, $viewName);

            $data = $this->templateFactory($filePath, $data);
        }

        res()->getBody()->write($data);
    }

    public function templateFactory(string $filePath, mixed $data = null): string
    {
        return '<h1>' . $filePath . '</h1><br><pre>' . json_encode($data) . '</pre>';
    }
}
